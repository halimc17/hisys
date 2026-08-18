<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$jab = getPostingJabatan('budget');
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
//$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkode=makeOption($dbname, 'bgt_kode', 'kodebudget,nama');
$nmakun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

$param['rupiah']=str_replace(",","",$param['rupiah']);
$param['jhk']   =str_replace(",","",$param['jhk']);
$param['jumlah']=str_replace(",","",$param['jumlah']);

$whr = " and kodeorg like '".$param['kodeorg']."%' and tipebudget='MILL' and tahunbudget='".$param['tahun']."' and pta='BGT' and kodebudget != 'UMUM'";
$tipebudget = 'MILL';

// echo"<pre>";
// print_r($param);
// echo"</pre>";
// exit("error");
switch ($method) {
	case'getTk':
	
		$tab="<table class=sortable cellspacing=1 cellpadding=2>";
		$tab.="<thead>";
		$tab.="<tr class=rowheader>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['nik2']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['gajipokok']."</th>";
		$tab.="<th align=center colspan=8>".$_SESSION['lang']['bulanan']."</th>";
		$tab.="<th align=center colspan=3>".$_SESSION['lang']['setahun']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['total']." ".$_SESSION['lang']['setahun']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['action']."</th>";
		$tab.="</tr>";
		$tab.="<tr class=rowheader>";
		$tab.="<th align=center>%</th>";
		$tab.="<th align=center>Gapok</th>";
		$tab.="<th align=center>Lembur</th>";
		$tab.="<th align=center>Premi</th>";
		$tab.="<th align=center>Tuntap</th>";
		$tab.="<th align=center>Tdk tetap</th>";
		$tab.="<th align=center>Exs Food</th>";
		$tab.="<th align=center>BPJS</th>";
		$tab.="<th align=center>THR</th>";
		$tab.="<th align=center>Bonus</th>";
		$tab.="<th align=center>Perumahan</th>";
		$tab.="</tr>";
		
		$tab.="</thead>";
		$tab.="<tbody>";
		
		$str = "select * from ".$dbname.".bgt_upahdetail where kodeorg= '".$param['station']."' and tahunbudget='".$param['tahunbudget']."'";
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$data[$val['karyawanid']]['gapok']=$val['gapok'];
			$data[$val['karyawanid']]['persengapok']=$val['persengapok'];
			$data[$val['karyawanid']]['premi']=$val['premi'];
			$data[$val['karyawanid']]['lembur']=$val['lembur'];
			$data[$val['karyawanid']]['tidaktetap']=$val['tidaktetap'];
			$data[$val['karyawanid']]['extrafooding']=$val['extrafooding'];
			$data[$val['karyawanid']]['tuntap']=$val['tuntap'];
			$data[$val['karyawanid']]['bpjs']=$val['bpjs'];
			$data[$val['karyawanid']]['thr']=$val['thr'];
			$data[$val['karyawanid']]['bonus']=$val['bonus'];
			$data[$val['karyawanid']]['perumahan']=$val['perumahan'];
			
			$data[$val['karyawanid']]['kanan']=(($val['gapok']+$val['lembur']+$val['tidaktetap']+$val['extrafooding']+$val['premi']+$val['tuntap']+$val['bpjs'])*12)+$val['thr']+$val['bonus']+$val['perumahan'];
			
			$bawah['gapok']+=$val['gapok'];
			$bawah['premi']+=$val['premi'];
			$bawah['lembur']+=$val['lembur'];
			$bawah['tidaktetap']+=$val['tidaktetap'];
			$bawah['extrafooding']+=$val['extrafooding'];
			$bawah['tuntap']+=$val['tuntap'];
			$bawah['bpjs']+=$val['bpjs'];
			$bawah['thr']+=$val['thr'];
			$bawah['bonus']+=$val['bonus'];
			$bawah['perumahan']+=$val['perumahan'];
			$bawah['kanan']+=(($val['gapok']+$val['lembur']+$val['tidaktetap']+$val['extrafooding']+$val['premi']+$val['tuntap']+$val['bpjs'])*12)+$val['thr']+$val['bonus']+$val['perumahan'];
		}
		
		
		$where=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tanggalmasuk<='".date("Y-m-d")."'";
		$where.=" and subbagian = '".$param['station']."' and tipekaryawan>0";
		$str = "select * from ".$dbname.".datakaryawan where 1=1 ".$where." order by kodejabatan, namakaryawan asc";
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			if($val['tipekaryawan']=='1'){
				$tipekary='SDM-KBL';
			}elseif($val['tipekaryawan']=='3'){
				$tipekary='SDM-KHT';
			}elseif($val['tipekaryawan']=='4'){
				$tipekary='SDM-PHL';
			}else{
				exit("Warning: Tipekaryawan tidak terdaftar.");
			}
			
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td hidden name=tipekary[]>".$tipekary."</td>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td hidden name=idkary[]>".$val['karyawanid']."</td>";
			$tab.="<td align=center>".$val['nik']."</td>";
			$tab.="<td align=left>".$val['namakaryawan']."</td>";
			$tab.="<td align=left>".getNamaTipeKary($val['tipekaryawan'])."</td>";
			$tab.="<td align=right nowrap id=gapokawal_".$no." name=gapokawal[]><span name=nilaigapokawal[] id=nilaigapokawal_".$no.">".number_format(getGapok($val['karyawanid']))."</span>  <img src=images\icons\arrow_right.png title='Set berdasarkan gapok lama.' class=zImgBtn onclick=copygapok(".$no.")></td>";
			
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:35px type=text id=persengapok_".$no." name=persengapok[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['persengapok']."></td>";
			
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=gapoknaik_".$no." name=gapoknaik[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['gapok']."></td>";
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=lembur_".$no." name=lembur[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['lembur']."></td>";
			
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=premi_".$no." name=premi[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['premi']."></td>";
			
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=tuntap_".$no." name=tuntap[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['tuntap']."></td>";
			
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=tidaktetap_".$no." name=tidaktetap[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['tidaktetap']."></td>";
			
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=extrafooding_".$no." name=extrafooding[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['extrafooding']."></td>";
			$tab.="<td><input onclick=delnol(this);  style=width:65px type=text id=bpjs_".$no." name=bpjs[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['bpjs']."></td>";
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=thr_".$no." name=thr[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['thr']."></td>";
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=bonus_".$no." name=bonus[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['bonus']."></td>";
			$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=rumah_".$no." name=rumah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$val['karyawanid']]['perumahan']."></td>";
			$tab.="<td align=right id=totalkanan_".$no." name=totalkanan[]>".$data[$val['karyawanid']]['kanan']."</td>";
			$tab.="<td align=center><img class=zImgBtn src='images/application/application_delete.png' onclick=delTK(".$no."); title=Delete></td>";
			$tab.="</tr>";
			
			$ttl+=getGapok($val['karyawanid']);
		}
		
		# rencana kary baru
		$no++;
		$karyawanid="4000000000";
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td hidden name=idkary[]>".$karyawanid."</td>";
		$tab.="<td align=center colspan=2>New Employee</td>";
		$tab.="<td name=tipekary[]>".$tipekary."</td>";
		$tab.="<td align=right id=gapokawal_".$no." name=gapokawal[]><span name=nilaigapokawal[] id=nilaigapokawal_".$no.">0</span></td>";
		
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:35px type=text id=persengapok_".$no." name=persengapok[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['persengapok']."></td>";
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this); style=width:65px type=text id=gapoknaik_".$no." name=gapoknaik[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['gapok']."></td>";
		
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=lembur_".$no." name=lembur[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['lembur']."></td>";
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this); style=width:65px type=text id=premi_".$no." name=premi[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['premi']."></td>";
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this); style=width:65px type=text id=tuntap_".$no." name=tuntap[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['tuntap']."></td>";
		
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=tidaktetap_".$no." name=tidaktetap[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['tidaktetap']."></td>";
			
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this.id); style=width:65px type=text id=extrafooding_".$no." name=extrafooding[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['extrafooding']."></td>";
		
		$tab.="<td><input onclick=delnol(this);  style=width:65px type=text id=bpjs_".$no." name=bpjs[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['bpjs']."></td>";
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this); style=width:65px type=text id=thr_".$no." name=thr[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['thr']."></td>";
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this); style=width:65px type=text id=bonus_".$no." name=bonus[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['bonus']."></td>";
		$tab.="<td><input onclick=delnol(this); onkeyup=hitungupah(".$no.",this); style=width:65px type=text id=rumah_".$no." name=rumah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$data[$karyawanid]['perumahan']."></td>";
		$tab.="<td align=right id=totalkanan_".$no." name=totalkanan[]>".$data[$karyawanid]['kanan']."</td>";
		$tab.="<td align=center><img class=zImgBtn src='images/application/application_delete.png' onclick=delTK(".$no."); title=Delete></td>";
		$tab.="</tr>";
		
		
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=4>TOTAL</td>";
		$tab.="<td align=right>".number_format($ttl)."</td>";
		$tab.="<td></td>";
		$tab.="<td><input disabled style=width:65px type=text id=gapoknaikbawah name=gapoknaikbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['gapok']."></td>";
		$tab.="<td><input disabled style=width:65px type=text id=lemburbawah name=lemburbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['lembur']."></td>";
		
		$tab.="<td><input disabled style=width:65px type=text id=premibawah name=premibawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['premi']."></td>";
		
		$tab.="<td><input disabled style=width:65px type=text id=tuntapbawah name=tuntapbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['tuntap']."></td>";
		
		$tab.="<td><input disabled style=width:65px type=text id=tidaktetapbawah name=tidaktetapbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['tidaktetap']."></td>";
		$tab.="<td><input disabled style=width:65px type=text id=extrafoodingbawah name=extrafoodingbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['extrafooding']."></td>";
		
		$tab.="<td><input disabled style=width:65px type=text id=bpjsbawah name=bpjsbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['bpjs']."></td>";
		$tab.="<td><input disabled style=width:65px type=text id=thrbawah name=thrbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['thr']."></td>";
		$tab.="<td><input disabled style=width:65px type=text id=bonusbawah name=bonusbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['bonus']."></td>";
		$tab.="<td><input disabled style=width:65px type=text id=rumahbawah name=rumahbawah[] class=myinputtextnumber nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\"/ value=".$bawah['perumahan']."></td>";
		$tab.="<td align=right style=background-color:gray;font-weight:bold; id=gtotalkanan name=gtotalkanan[]>".$bawah['kanan']."</td>";
		$tab.="<td align=center></td>";
		$tab.="</tr>";
		$tab.="</tbody>";
		$tab.="<tfoot>";
		$tab.="<tr>";
		
		$tab.="<td colspan=18 align=center><button class=mybutton onclick=simpanTk()>Simpan</button></td>";
		
		$tab.="</tr>";
		$tab.="</tfoot>";
		$tab.="</table>";
		
		echo $tab;
	break;
	case'sebartt':
		try{
			$owlPDO->beginTransaction();
			
			$ttlpersen=0;
			for($i==1;$i<=12;$i++){
				if($param['persen'][$i]==''){$param['persen'][$i]=0;}
				$ttlpersen+=$param['persen'][$i];
			}
			if($ttlpersen==0){
				throw new PDOException("Persen sebaran belum ada.");
			}
			
			$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = 'MILL' and pta='BGT' and kodebudget !='UMUM' and kodeorg like '".$param['station']."%'";
			$res=fetchdata($str);
			if(count($res)>0){				
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					if($bar['tutup']=='1'){
						throw new PDOException("Budget sudah ditutup.");
					}
					$str="insert into ".$dbname.".bgt_distribusi (`kunci`";
					for($i=1;$i<=12;$i++){
						$str.=",`rp".addZero($i,2)."`";
						$str.=",`fis".addZero($i,2)."`";
					}
					$str.=") values('".$bar['kunci']."'";
					for($i=1;$i<=12;$i++){
						$str.=",'".$param['persen'][$i]/$ttlpersen*$bar['rupiah']."'";
						$str.=",'".$param['persen'][$i]/$ttlpersen*$bar['jumlah']."'";
					}
					$str.=");";
					$owlPDO->exec($str);
				}
			}
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'sebardetail':
		try{
			$owlPDO->beginTransaction();
			
			$ttlpersen=0;
			for($i==1;$i<=12;$i++){
				if($param['persen'][$i]==''){$param['persen'][$i]=0;}
				$ttlpersen+=$param['persen'][$i];
			}
			if($ttlpersen==0){
				throw new PDOException("Persen sebaran belum ada.");
			}
			if(is_array($param['index'])){				
				foreach($param['index'] as $key => $kunci){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$kunci."'";
					$owlPDO->exec($str);

					$str="select * from ".$dbname.".bgt_budget where kunci='".$kunci."'";
					$res=fetchdata($str);
					foreach($res as $bar){
						if($bar['tutup']=='1'){
							throw new PDOException("Budget sudah ditutup.");
						}
						$str="insert into ".$dbname.".bgt_distribusi (`kunci`";
						for($i=1;$i<=12;$i++){
							$str.=",`rp".addZero($i,2)."`";
							$str.=",`fis".addZero($i,2)."`";
						}
						$str.=") values('".$kunci."'";
						for($i=1;$i<=12;$i++){
							$str.=",'".$param['persen'][$i]/$ttlpersen*$bar['rupiah']."'";
							$str.=",'".$param['persen'][$i]/$ttlpersen*$bar['jumlah']."'";
						}
						$str.=");";
						$owlPDO->exec($str);
					}
				}
			}
		
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'sebarperstation':
		try{
			$owlPDO->beginTransaction();
			
			$ttlpersen=0;
			for($i==1;$i<=12;$i++){
				if($param['persen'][$i]==''){$param['persen'][$i]=0;}
				$ttlpersen+=$param['persen'][$i];
			}
			if($ttlpersen==0){
				throw new PDOException("Persen sebaran belum ada.");
			}
			
			$where="";
			if($param['kodebarang']!=''){
				$where.=" and kodebarang='".$param['kodebarang']."'";
			}
			if($param['kodevhc']!=''){
				$where.=" and kodevhc='".$param['kodevhc']."'";
			}
			
			$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = 'MILL' and pta='BGT' and kodebudget !='UMUM' and kodeorg like '".$param['station']."%' and kodebudget='".$param['kodebudget']."' ".$where."";
			// echo $str;
			// exit("error");
			$res=fetchdata($str);
			if(count($res)>0){				
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					if($bar['tutup']=='1'){
						throw new PDOException("Budget sudah ditutup.");
					}
					$str="insert into ".$dbname.".bgt_distribusi (`kunci`";
					for($i=1;$i<=12;$i++){
						$str.=",`rp".addZero($i,2)."`";
						$str.=",`fis".addZero($i,2)."`";
					}
					$str.=") values('".$bar['kunci']."'";
					for($i=1;$i<=12;$i++){
						$str.=",'".$param['persen'][$i]/$ttlpersen*$bar['rupiah']."'";
						$str.=",'".$param['persen'][$i]/$ttlpersen*$bar['jumlah']."'";
					}
					$str.=");";
					$owlPDO->exec($str);
				}
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'formcaribarang':
		echo"<table>
				<tr>
					<td>Find</td>
					<td><input type=text class=myinputtext id=kodebarangcari onkeypress='enterkey(event,caribarang)' style=width:145px;></td>
					<td><button class=mybutton onclick='caribarang()'>".$_SESSION['lang']['find']."</button></td>
				</tr>
			</table>";
		echo"<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center width=70px>".$_SESSION['lang']['kodebarang']."</th>
					<th align=center>".$_SESSION['lang']['namabarang']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['harga']."</th>
				</tr>
			</thead><tbody id=contcaribarang></tbody>
			</table>
			<input hidden id=sumbermat value='".$param['sumber']."'>
			";
	break;
	case'caribarang':
		
		if($nmBrg==''){
			@$nmBrg=$kdBarang;
		}

		$str = "select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$whr="";
		if(strlen($param['klbarang'])<3){			
			$whr.=" and kodebarang like '".$param['klbarang']."%'";
		}else{			
			if($param['klbarang']!=''){
				$whr.=" and left(kodebarang,3)='".$param['klbarang']."'";
			}
		}
		
		if($param['kodebarang']!=''){
			$whr.=" and kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where kodebarang like '%".$param['kodebarang']."%' or namabarang like '%".$param['kodebarang']."%')";
		}
		$no=0;
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' ".$whr." ";
		$res=fetchData($str);
		foreach($res as $bar){
			$s="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$bar['kodebarang']."'";
			$nm=fetchData($s)[0];
			
			$no+=1;
			if($bar['hargasatuan']>0){
				$set="style=cursor:pointer onclick=\"setdata('".$bar['kodebarang']."','".$nm['namabarang']."','".trim($nm['satuan'])."','".$bar['hargasatuan']."')\"";
			}else{
				$set="style=background-color:#FEE0B9; title=\"Harga barang belum ada.\"";
			}
			$tab.="<tr class=rowcontent ".$set.">";				
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['kodebarang']."</td>";
			$tab.="<td>".$nm['namabarang']."</td>";
			$tab.="<td align=center>".$nm['satuan']."</td>";
			$tab.="<td align=right>".@number_format($bar['hargasatuan'])."</td>";
			$tab.="</tr>";
		}

		echo $tab;
	break;
	case'simpanvhc':
		cekheader($param);
	
		try{
			$owlPDO->beginTransaction();
			
			if($param['kdbudget']==''){
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if($param['kodevhc']==''){
				throw new PDOException("Kode kendaraan wajib diisi.");
			}
			if($param['jumlah']=='' or $param['jumlah']=='0'){
				throw new PDOException("Jumlah wajib diisi.");
			}
			if($param['rupiah']=='' or $param['rupiah']=='0'){
				throw new PDOException("Rupiah setahun wajib diisi.");
			}
			
			$param['jumlah']=round($param['jumlah'],5);
			$str = "select * from ".$dbname.".bgt_kode where kodebudget = '".$param['kdbudget']."'";
			$res = fetchdata($str)[0];
			$param['noakun'] = $res['noakun'];
			
			if($param['noakun']==''){
				throw new PDOException("Noakun ".$param['kdbudget']." belum disetting. Silahkan disetting terlebih dahulu melalui menu : Anggaran - Setup - Kode Budget.");
			}	
			
			if($nmakun[$param['noakun']]==''){
				throw new PDOException("Noakun ".$param['noakun']." tidak terdaftar di Keuangan - Setup - Daftar Perkiraan.");
			}
			
			#permintaan rudi don bosco aruskas di vra dikosongkan
			$param['aruskas']="";
			if($param['aruskas']==''){
				//throw new PDOException("Arus kas tidak boleh kosong.");
			}
			
			if($param['update']=='update'){
				$str="select * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$param['tahun']."' and kodevhc='".$param['kodevhc']."' and unitalokasi='".$param['kodeorg']."'";
				$res=fetchdata($str);
				$tersedia=$res[0]['jumlahjam'];
				
				$str="select sum(jumlah) as jumlah from ".$dbname.".bgt_budget where tahunbudget='".$param['tahun']."' and kodevhc='".$param['kodevhc']."' and tipebudget<>'TRK' and left(kodeorg,4)='".$param['kodeorg']."' and kunci !='".$param['index']."' group by left(kodeorg,4)";
				$res=fetchdata($str);
				$teralokasi=$res[0]['jumlah'];
				
				$sisa=$tersedia-$teralokasi;
			
				if($param['jumlah']>$sisa){
				  throw new PDOException("Total HM/KM Kendaraan ".$param['kodevhc']." :\nTersedia = ".number_format($tersedia,2)."\nSudah teralokasi = ".number_format($teralokasi,2)."\nSisa = ".number_format($sisa,2)."");
				}
				
				
				$data = array(
					'tahunbudget'=> $param['tahun'],
					'kodeorg'    => $param['blok'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'noakun'     => $param['noakun'],
					'rupiah'     => $param['rupiah'],
					'updateby'   => $_SESSION['standard']['userid'],
					'volume'     => $param['jumlah'],
					'satuanv'    => $param['satuan'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'aruskas'    => $param['aruskas']
				);
				
				$where = "kunci='".$param['index']."'";
				$query = updateQuery($dbname,'bgt_budget',$data,$where); #exit("error".$query);
				$owlPDO->exec($query);
				
			}else{
				$wh="";
				if($param['mesin']!=''){
					$whr="and `kodeorg` like '".$param['mesin']."%'";
					$wh.="and `kodeorganisasi` like '".$param['mesin']."%'";
				}else{
					$whr="and `kodeorg` like '".$param['station']."%'";
					$wh.="and `induk` = '".$param['station']."'";
				}
				$whr.=" and kodevhc='".$param['kodevhc']."'";
				
				$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = '".$tipebudget."' ".$whr." and `kodebudget` = '".$param['kdbudget']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
				}
				
				
				$str="select * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$param['tahun']."' and kodevhc='".$param['kodevhc']."' and unitalokasi='".$param['kodeorg']."'";
				$res=fetchdata($str);
				$tersedia=$res[0]['jumlahjam'];
				
				$str="select sum(jumlah) as jumlah from ".$dbname.".bgt_budget where tahunbudget='".$param['tahun']."' and kodevhc='".$param['kodevhc']."' and tipebudget<>'TRK' and left(kodeorg,4)='".$param['kodeorg']."' group by left(kodeorg,4)";
				$res=fetchdata($str);
				$teralokasi=$res[0]['jumlah'];
				
				$sisa=$tersedia-$teralokasi;
			
				if($param['jumlah']>$sisa){
				  throw new PDOException("Total HM/KM Kendaraan ".$param['kodevhc']." :\nTersedia = ".number_format($tersedia,2)."\nSudah teralokasi = ".number_format($teralokasi,2)."\nSisa = ".number_format($sisa,2)."");
				}
				
				
				$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
				$res = fetchdata($str);
				$jlh = count($res);
				if($jlh>0){
					$no=0;$tvol=$tjlh=$trp=$jumlah=0;
					foreach($res as $bar){
						$no++;
						if($no<$jlh){
							$jumlah = round(($param['jumlah']/$jlh),5);
							$tjlh+=$jumlah;
							
							$totalrp= round(($param['rupiah']/$jlh),0);							
							$trp+=$totalrp;
						}else{
							$jumlah = $param['jumlah']-$tjlh;
							$totalrp= $param['rupiah']-$trp;
						}
						
						$data = array(
							'tahunbudget'=> $param['tahun'],
							'kodeorg'    => $bar['kodeorganisasi'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'noakun'     => $param['noakun'],
							'rupiah'     => $totalrp,
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'volume'     => $jumlah,
							'satuanv'    => $param['satuan'],
							'satuanj'    => $param['satuan'],
							'aruskas'    => $param['aruskas'],
							'kodevhc'    => $param['kodevhc']
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname,'bgt_budget',$data,$cols);
						$owlPDO->exec($query);
					}
				}
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'simpankont':
		cekheader($param);
	
		try{
			$owlPDO->beginTransaction();
			
			if($param['kdbudget']==''){
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if($param['noakun']==''){
				throw new PDOException("Kode akun wajib diisi.");
			}
			if($param['satuan']==''){
				throw new PDOException("Satuan wajib diisi.");
			}
			if($param['jumlah']=='' or $param['jumlah']=='0'){
				throw new PDOException("Jumlah wajib diisi.");
			}
			if($param['rupiah']=='' or $param['rupiah']=='0'){
				throw new PDOException("Rupiah setahun wajib diisi.");
			}
			
			$param['jumlah']=round($param['jumlah'],5);
			
			if($param['update']=='update'){
				$data = array(
					'tahunbudget'    => $param['tahun'],
					'kodeorg'        => $param['mesin'],
					'tipebudget'     => $tipebudget,
					'kodebudget'     => $param['kdbudget'],
					'kodebarang'     => $param['kodebarang'],
					'noakun'         => $param['noakun'],
					'rupiah'         => $param['rupiah'],
					'updateby'       => $_SESSION['standard']['userid'],
					'volume'         => $param['jumlah'],
					'satuanv'        => $param['satuan'],
					'jumlah'         => $param['jumlah'],
					'satuanj'        => $param['satuan'],
					'keterangan'     => $param['keterangan'],
					'aruskas'        => $param['aruskas']
				);
				
				$where = "kunci='".$param['index']."'";
				$query = updateQuery($dbname,'bgt_budget',$data,$where); #exit("error".$query);
				$owlPDO->exec($query);
				
			}else{
				$wh="";
				if($param['mesin']!=''){
					$whr="and `kodeorg` like '".$param['mesin']."%'";
					$wh.="and `kodeorganisasi` like '".$param['mesin']."%'";
				}else{
					$whr="and `kodeorg` like '".$param['station']."%'";
					$wh.="and `induk` = '".$param['station']."'";
				}
				
				$whr.="and `noakun` = '".$param['noakun']."'";
				if($param['keterangan']!=''){					
					$whr.="and `keterangan` = '".$param['keterangan']."'";
				}else{
					$whr.="and (`keterangan` = '' or `keterangan` is null)";
				}
				$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = '".$tipebudget."' ".$whr." and `kodebudget` = '".$param['kdbudget']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
				}
				
				$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
				$res = fetchdata($str);
				$jlh = count($res);
				if($jlh>0){
					$no=0;$tvol=$tjlh=$trp=$jumlah=0;
					foreach($res as $bar){
						$no++;
						if($no<$jlh){
							$jumlah = round(($param['jumlah']/$jlh),5);
							$tjlh+=$jumlah;
							
							$totalrp= round(($param['rupiah']/$jlh),0);							
							$trp+=$totalrp;
						}else{
							$jumlah = $param['jumlah']-$tjlh;
							$totalrp= $param['rupiah']-$trp;
						}
						
						$data = array(
							'tahunbudget'=> $param['tahun'],
							'kodeorg'    => $bar['kodeorganisasi'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'noakun'     => $param['noakun'],
							'kodebarang' => $param['kodebarang'],
							'rupiah'     => $totalrp,
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'volume'     => $jumlah,
							'satuanv'    => $param['satuan'],
							'satuanj'    => $param['satuan'],
							'keterangan' => $param['keterangan'],
							'aruskas'    => $param['aruskas']
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname,'bgt_budget',$data,$cols);
						$owlPDO->exec($query);
					}
				}
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'simpanlain':
		cekheader($param);
	
		try{
			$owlPDO->beginTransaction();
			
			if($param['kdbudget']==''){
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if($param['noakun']==''){
				throw new PDOException("Kode akun wajib diisi.");
			}
			if($param['keterangan']==''){
				throw new PDOException("Keterangan akun wajib diisi.");
			}
			if($param['rupiah']=='' or $param['rupiah']=='0'){
				throw new PDOException("Rupiah setahun wajib diisi.");
			}
			
			$param['jumlah']=round($param['jumlah'],5);
			
			if($param['update']=='update'){
				$data = array(
					'tahunbudget'=> $param['tahun'],
					'kodeorg'    => $param['mesin'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'noakun'     => $param['noakun'],
					'rupiah'     => $param['rupiah'],
					'updateby'   => $_SESSION['standard']['userid'],
					'volume'     => $param['jumlah'],
					'satuanv'    => $param['satuan'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'keterangan' => $param['keterangan'],
					'aruskas'    => $param['aruskas']
				);
				
				$where = "kunci='".$param['index']."'";
				$query = updateQuery($dbname,'bgt_budget',$data,$where); #exit("error".$query);
				$owlPDO->exec($query);
				
			}else{
				$wh="";
				if($param['mesin']!=''){
					$whr="and `kodeorg` like '".$param['mesin']."%'";
					$wh.="and `kodeorganisasi` like '".$param['mesin']."%'";
				}else{
					$whr="and `kodeorg` like '".$param['station']."%'";
					$wh.="and `induk` = '".$param['station']."'";
				}
				
				$whr.="and `noakun` = '".$param['noakun']."'";
				if($param['keterangan']!=''){					
					$whr.="and `keterangan` = '".$param['keterangan']."'";
				}else{
					$whr.="and (`keterangan` = '' or `keterangan` is null)";
				}
				
				$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = '".$tipebudget."' ".$whr." and `kodebudget` = '".$param['kdbudget']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
				}
				
				$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
				$res = fetchdata($str);
				$jlh = count($res);
				if($jlh>0){
					$no=0;$tvol=$tjlh=$trp=$jumlah=0;
					foreach($res as $bar){
						$no++;
						if($no<$jlh){
							$jumlah = round(($param['jumlah']/$jlh),5);
							$tjlh+=$jumlah;
							
							$totalrp= round(($param['rupiah']/$jlh),0);							
							$trp+=$totalrp;
						}else{
							$jumlah = $param['jumlah']-$tjlh;
							$totalrp= $param['rupiah']-$trp;
						}
						
						$data = array(
							'tahunbudget'=> $param['tahun'],
							'kodeorg'    => $bar['kodeorganisasi'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'noakun'     => $param['noakun'],
							'rupiah'     => $totalrp,
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'volume'     => $jumlah,
							'satuanv'    => $param['satuan'],
							'satuanj'    => $param['satuan'],
							'keterangan' => $param['keterangan'],
							'aruskas'    => $param['aruskas']
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname,'bgt_budget',$data,$cols);
						$owlPDO->exec($query);
					}
				}
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'simpanmain':
		cekheader($param);
	
		try{
			$owlPDO->beginTransaction();
			
			if($param['kdbudget']==''){
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if($param['jumlah']==''){
				$param['jumlah']=='0';
			}
			
			$param['satuan']='';
			if($param['kdbudget']=='SERVICE'){
				$param['satuan']='JAM';
				if($param['kodews']==''){
					throw new PDOException("Kode workshop wajib diisi.");					
				}
				if($param['jumlah']=='' or $param['jumlah']=='0'){
					throw new PDOException("Jumlah jam wajib diisi.");					
				}
			}
			
			if($param['rupiah']=='' or $param['rupiah']=='0'){
				throw new PDOException("Rupiah setahun wajib diisi.");
			}
			$str = "select * from ".$dbname.".bgt_kode where kodebudget = '".$param['kdbudget']."'";
			$res = fetchdata($str)[0];
			$param['noakun'] = $res['noakun'];
			
			if($param['noakun']==''){
				throw new PDOException("Noakun ".$param['kdbudget']." belum disetting. Silahkan disetting terlebih dahulu melalui menu : Anggaran - Setup - Kode Budget.");
			}	
			
			if($nmakun[$param['noakun']]==''){
				throw new PDOException("Noakun ".$param['noakun']." tidak terdaftar di Keuangan - Setup - Daftar Perkiraan.");
			}
			
			if($param['update']=='update'){
				$data = array(
					'tahunbudget'=> $param['tahun'],
					'kodeorg'    => $param['mesin'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'noakun'     => $param['noakun'],
					'kodews'     => $param['kodews'],
					'rupiah'     => $param['rupiah'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'aruskas'    => $param['aruskas']
				);
				
				$where = "kunci='".$param['index']."'";
				$query = updateQuery($dbname,'bgt_budget',$data,$where); #exit("error".$query);
				$owlPDO->exec($query);
				
			}else{
				$wh="";
				if($param['mesin']!=''){
					$whr="and `kodeorg` like '".$param['mesin']."%'";
					$wh.="and `kodeorganisasi` like '".$param['mesin']."%'";
				}else{
					$whr="and `kodeorg` like '".$param['station']."%'";
					$wh.="and `induk` = '".$param['station']."'";
				}
				
				$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = '".$tipebudget."' ".$whr." and `kodebudget` = '".$param['kdbudget']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
				}
				
				$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
				$res = fetchdata($str);
				$jlh = count($res);
				if($jlh>0){
					$no=0;$tvol=$tjlh=$trp=$jumlah=0;
					foreach($res as $bar){
						$no++;
						if($no<$jlh){
							if($param['jumlah']>0){
								$jumlah = round(($param['jumlah']/$jlh),5);
								$tjlh+=$jumlah;
							}
							
							$totalrp= round(($param['rupiah']/$jlh),0);							
							$trp+=$totalrp;
						}else{
							if($param['jumlah']>0){								
								$jumlah = $param['jumlah']-$tjlh;
							}
							$totalrp= $param['rupiah']-$trp;
						}
						
						$data = array(
							'tahunbudget'=> $param['tahun'],
							'kodeorg'    => $bar['kodeorganisasi'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'noakun'     => $param['noakun'],
							'kodews'     => $param['kodews'],
							'rupiah'     => $totalrp,
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => $param['satuan'],
							'aruskas'    => $param['aruskas']
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname,'bgt_budget',$data,$cols);
						$owlPDO->exec($query);
					}
				}
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
	case'simpanmat':
		cekheader($param);
	
		try{
			$owlPDO->beginTransaction();
			
			if($param['noakun']==''){
				throw new PDOException("Nomor Akun wajib diisi.");
			}
			if($param['kdbudget']==''){
				throw new PDOException("Kelompok Barang wajib diisi.");
			}
			if($param['kodebarang']==''){
				throw new PDOException("Kode barang wajib diisi.");
			}
			if($param['jenis']==''){
				throw new PDOException("Jenis wajib diisi.");
			}
			if($param['jumlah']=='' or $param['jumlah']=='0'){
				throw new PDOException("Jumlah wajib diisi.");
			}
			if($param['rupiah']=='' or $param['rupiah']=='0'){
				throw new PDOException("Rupiah setahun wajib diisi.");
			}
			
			$param['jumlah']=round($param['jumlah'],5);

			$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
			$res = fetchdata($str);
			$region = $res[0]['regional'];
			
			if($param['update']=='update'){
				$data = array(
					'tahunbudget'=> $param['tahun'],
					'kodeorg'    => $param['mesin'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'noakun'     => $param['noakun'],
					'rupiah'     => $param['rupiah'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jumlah'],
					'satuanj'    => $param['satuan'],
					'aruskas'    => $param['aruskas'],
					'kodebarang' => $param['kodebarang'],
					'keterangan' => $param['jenis'],
					'regional'   => $region
				);
				
				$where = "kunci='".$param['index']."'";
				$query = updateQuery($dbname,'bgt_budget',$data,$where); #exit("error".$query);
				$owlPDO->exec($query);
				
			}else{
				$wh="";
				if($param['mesin']!=''){
					$whr="and `kodeorg` like '".$param['mesin']."%'";
					$wh.="and `kodeorganisasi` like '".$param['mesin']."%'";
				}else{
					$whr="and `kodeorg` like '".$param['station']."%'";
					$wh.="and `induk` = '".$param['station']."'";
				}
				
				$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = '".$tipebudget."' ".$whr." and `kodebudget` = '".$param['kdbudget']."' and `kodebarang` = '".$param['kodebarang']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
				}
				
				$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
				$res = fetchdata($str);
				$jlh = count($res);
				if($jlh>0){
					$no=0;$tjlh=$trp=0;
					foreach($res as $bar){
						$no++;
						if($no<$jlh){
							$jumlah = round(($param['jumlah']/$jlh),5);
							$totalrp= round(($param['rupiah']/$jlh),0);
							
							$tjlh+=$jumlah;
							$trp+=$totalrp;
						}else{
							$jumlah = $param['jumlah']-$tjlh;
							$totalrp= $param['rupiah']-$trp;
						}
						
						$data = array(
							'tahunbudget'=> $param['tahun'],
							'kodeorg'    => $bar['kodeorganisasi'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'noakun'     => $param['noakun'],
							'rupiah'     => $totalrp,
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => $param['satuan'],
							'aruskas'    => $param['aruskas'],
							'kodebarang' => $param['kodebarang'],
							'keterangan' => $param['jenis'],
							'regional'   => $region
							
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname,'bgt_budget',$data,$cols);
						$owlPDO->exec($query);
					}
				}
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
	case'simpansdm':
		cekheader($param);
	
		try{
			$owlPDO->beginTransaction();
			
			if($param['kdbudget']==''){
				throw new PDOException("Kode anggaran wajib diisi.");
			}
			if($param['hke']==''){
				throw new PDOException("HKE wajib diisi.");
			}
			if($param['jlhtk']==''){
				throw new PDOException("Jumlah TK wajib diisi.");
			}
			if($param['jhk']==''){
				throw new PDOException("Jumlah HK wajib diisi.");
			}
			if($param['rupiah']=='' or $param['rupiah']=='0'){
				throw new PDOException("Rupiah setahun wajib diisi.");
			}
			
			$param['jumlah']=round($param['jumlah'],5);
			
			$str = "select * from ".$dbname.".bgt_kode where kodebudget = '".$param['kdbudget']."'";
			$res = fetchdata($str)[0];
			$param['noakun'] = $res['noakun'];
			
			if($param['noakun']==''){
				throw new PDOException("Noakun ".$param['kdbudget']." belum disetting. Silahkan disetting terlebih dahulu melalui menu : Anggaran - Setup - Kode Budget.");
			}	
			
			if($nmakun[$param['noakun']]==''){
				throw new PDOException("Noakun ".$param['noakun']." tidak terdaftar di Keuangan - Setup - Daftar Perkiraan.");
			}
			
			if($param['update']=='update'){
				$data = array(
					'tahunbudget'=> $param['tahun'],
					'kodeorg'    => $param['mesin'],
					'tipebudget' => $tipebudget,
					'kodebudget' => $param['kdbudget'],
					'noakun'     => $param['noakun'],
					'rupiah'     => $param['rupiah'],
					'updateby'   => $_SESSION['standard']['userid'],
					'jumlah'     => $param['jhk'],
					'satuanj'    => 'HK',
					'aruskas'    => $param['aruskas']
				);
				
				$where = "kunci='".$param['index']."'";
				$query = updateQuery($dbname,'bgt_budget',$data,$where); #exit("error".$query);
				$owlPDO->exec($query);
				
			}else{
				$wh="";
				if($param['mesin']!=''){
					$whr="and `kodeorg` like '".$param['mesin']."%'";
					$wh.="and `kodeorganisasi` like '".$param['mesin']."%'";
				}else{
					$whr="and `kodeorg` like '".$param['station']."%'";
					$wh.="and `induk` = '".$param['station']."'";
				}
				
				$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = '".$tipebudget."' ".$whr." and `kodebudget` = '".$param['kdbudget']."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
					
					$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
					$owlPDO->exec($str);
				}
				
				$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
				$res = fetchdata($str);
				$jlh = count($res);
				if($jlh>0){
					$no=0;$tjlh=$trp=0;
					foreach($res as $bar){
						$no++;
						if($no<$jlh){
							$jumlah = round(($param['jhk']/$jlh),5);
							$totalrp= round(($param['rupiah']/$jlh),0);
							
							$tjlh+=$jumlah;
							$trp+=$totalrp;
						}else{
							$jumlah = $param['jhk']-$tjlh;
							$totalrp= $param['rupiah']-$trp;
						}
						
						$data = array(
							'tahunbudget'=> $param['tahun'],
							'kodeorg'    => $bar['kodeorganisasi'],
							'tipebudget' => $tipebudget,
							'kodebudget' => $param['kdbudget'],
							'noakun'     => $param['noakun'],
							'rupiah'     => $totalrp,
							'updateby'   => $_SESSION['standard']['userid'],
							'jumlah'     => $jumlah,
							'satuanj'    => 'HK',
							'aruskas'    => $param['aruskas']
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname,'bgt_budget',$data,$cols);
						$owlPDO->exec($query);
					}
				}
			}
			
			
				$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'simpanTk':
		cekheader($param);
		
		try{
		$owlPDO->beginTransaction();
			$param['tipebudget']='MILL';
			$tipekary='EXPL-UPAH';
			$param['kdbudget']='EXPL-UPAH';
			
			$jlhtk=0; $param['rupiah']=0;
			foreach($param['idkary'] as $key => $karyid){
				$param['totalkanan'][$key] = str_replace(",","",$param['totalkanan'][$key]);
				if($param['totalkanan'][$key]>0){
					$jlhtk+=1;
					$param['rupiah']+=$param['totalkanan'][$key];
				}
			}
			
			$param['jhk']=$param['hkefektif']*$jlhtk;
			
			$str = "select * from ".$dbname.".bgt_kode where kodebudget = '".$param['kdbudget']."'";
			$res = fetchdata($str)[0];
			$param['noakun'] = $res['noakun'];
			
			if($param['noakun']==''){
				throw new PDOException("Noakun ".$param['kdbudget']." belum disetting. Silahkan disetting terlebih dahulu melalui menu : Anggaran - Setup - Kode Budget.");
			}	
			if($param['aruskas']==''){
				#throw new PDOException("Arus kas untuk akun ".$param['noakun']." belum ada.");
			}	
			
			if($nmakun[$param['noakun']]==''){
				throw new PDOException("Noakun ".$param['noakun']." tidak terdaftar di Keuangan - Setup - Daftar Perkiraan.");
			}
			
			$str = "select * from ".$dbname.".bgt_budget where tipebudget = '".$param['tipebudget']."' and tahunbudget ='".$param['tahunbudget']."' and kodeorg like '".$param['kodeorg']."%' and kodeorg like '".$param['station']."%' and kodebudget ='".$tipekary."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
				$owlPDO->exec($str);
				
				$str = "delete from ".$dbname.".bgt_budget  where tahunbudget ='".$param['tahunbudget']."' and kunci='".$bar['kunci']."'";
				$owlPDO->exec($str);				
			}
			
			$str = "delete from ".$dbname.".bgt_upahdetail  where tahunbudget ='".$param['tahunbudget']."' and kodeorg='".$param['station']."' and golongan='".$tipekary."'";
			$owlPDO->exec($str);
			
			
			$wh="";
			$wh.="and `induk` = '".$param['station']."'";
			$str = "select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
			$res = fetchdata($str);
			$jlh = count($res);
			if($jlh>0){
				$no=0;$tjlh=$trp=0;
				foreach($res as $bar){
					$no++;
					if($no<$jlh){
						
						$jumlah = round(($param['jhk']/$jlh),5);
						$totalrp= round(($param['rupiah']/$jlh),0);
						
						$tjlh+=$jumlah;
						$trp+=$totalrp;
					}else{
						$jumlah = $param['jhk']-$tjlh;
						$totalrp= $param['rupiah']-$trp;
					}
					
					$data = array(
						'tahunbudget'=> $param['tahun'],
						'kodeorg'    => $bar['kodeorganisasi'],
						'tipebudget' => $param['tipebudget'],
						'kodebudget' => $tipekary,
						'noakun'     => $param['noakun'],
						'volume'     => $jlhtk,
						'satuanv'    => 'Orang',
						'rupiah'     => $totalrp,
						'updateby'   => $_SESSION['standard']['userid'],
						'jumlah'     => $jumlah,
						'satuanj'    => 'HK',
						'aruskas'    => $param['aruskas']
					);
					
					$cols = array();
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}

					$query = insertQuery($dbname,'bgt_budget',$data,$cols);
					$owlPDO->exec($query);
				}
			}
			
			foreach($param['idkary'] as $key => $karyid){
				$param['gapoknaik'][$key]    = str_replace(",","",$param['gapoknaik'][$key]);
				$param['premi'][$key]        = str_replace(",","",$param['premi'][$key]);
				$param['tuntap'][$key]       = str_replace(",","",$param['tuntap'][$key]);
				$param['bpjs'][$key]         = str_replace(",","",$param['bpjs'][$key]);
				$param['thr'][$key]          = str_replace(",","",$param['thr'][$key]);
				$param['bonus'][$key]        = str_replace(",","",$param['bonus'][$key]);
				$param['rumah'][$key]        = str_replace(",","",$param['rumah'][$key]);
				$param['totalkanan'][$key]   = str_replace(",","",$param['totalkanan'][$key]);
				$param['lembur'][$key]       = str_replace(",","",$param['lembur'][$key]);
				$param['tidaktetap'][$key]   = str_replace(",","",$param['tidaktetap'][$key]);
				$param['extrafooding'][$key] = str_replace(",","",$param['extrafooding'][$key]);
				$param['persengapok'][$key]  = str_replace(",","",$param['persengapok'][$key]);
				$param['gapokbefore'][$key]  = str_replace(",","",$param['gapokbefore'][$key]);
				
				$str = "select * from ".$dbname.".bgt_upahdetail where kodeorg = '".$param['station']."' and tahunbudget ='".$param['tahunbudget']."' and golongan ='".$tipekary."' and karyawanid ='".$param['idkary'][$key]."'";
				$res = fetchdata($str);
				if(count($res)>0){					
					throw new PDOException("Karyawan ".getKary($karyid)." sudah pernah terdaftar.");
				}
				
				if($param['totalkanan'][$key]>0){
					$data = array();
					$data = array(
						'id'            => $param['station'],
						'tahunbudget'   => $param['tahunbudget'],
						'kodeorg'       => $param['station'],
						'golongan'      => $tipekary,
						'karyawanid'    => $param['idkary'][$key],
						'gapokbefore'   => $param['gapokbefore'][$key],
						'gapok'         => $param['gapoknaik'][$key],
						'persengapok'   => $param['persengapok'][$key],
						'lembur'        => $param['lembur'][$key],
						'premi'         => $param['premi'][$key],
						'tuntap'        => $param['tuntap'][$key],
						'tidaktetap'    => $param['tidaktetap'][$key],
						'extrafooding'  => $param['extrafooding'][$key],
						'bpjs'          => $param['bpjs'][$key],
						'thr'           => $param['thr'][$key],
						'bonus'         => $param['bonus'][$key],
						'perumahan'     => $param['rumah'][$key],
						'updateby'      => $_SESSION['standard']['userid']
					);
					$cols = array();
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$query = insertQuery($dbname,'bgt_upahdetail',$data,$cols);
					$owlPDO->exec($query);
				}
			}
			
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}	
		
	break;
	
	case'getupah':
		if($param['kdbudget']==''){
		   exit("Warning : Kode anggaran wajib diisi.");
		}
		$str="select jumlah from ".$dbname.".bgt_upah where tahunbudget='".$param['tahun']."' and kodeorg = '".$param['kodeorg']."' and golongan='".$param['kdbudget']."' and closed=1";
		$res=fetchdata($str);
		if(count($res)>0){
			if($res[0]['jumlah']==''){
				exit("Warning : Data upah belum ada, silahkan cek kembali");
			}else{
				$totalupah = (floatval($res[0]['jumlah'])*floatval($param['jhk']));
				$totupah = number_format($totalupah);
			}
		}else{
			exit("Error : Budget upah rata - rata belum diinput atau ditutup.");
		}
		
		echo $totupah;
	break;
	case'getkodews':
		$str = "select * from ".$dbname.".organisasi where induk='".$param['kodeorg']."' and tipe in ('WORKSHOP','MAINTENANCE')"; #exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$ws.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
		echo $ws;
	break;
	case'getjumlahws':
		$str="select distinct rpperjam from ".$dbname.".bgt_biaya_ws_per_jam where tahunbudget='".$param['tahun']."' and kodews='".$param['kodews']."'";
		$res=fetchdata($str)[0];
		if(count($res)>0){
			$hasil=floatval($res['rpperjam'])*floatval($param['jamws']);
			echo $hasil;
		}else{
			exit("Warning : Jumlah jam bengkel tidak tersedia.");
		}
		
	break;
	
	case'getaruskas':
	
		if($param['akun']=='x'){		
			$optakun=makeOption($dbname,'bgt_kode','kodebudget,noakun');
			$param['akun']=$optakun[$param['kdbudget']];
		}
		$wh="";
		if($param['kontpks']=='kontpks'){
			if($_SESSION['empl']['tipelokasitugas']=='BULKING'){
				$wh.=" and (b.noakun like '812%' or b.noakun like '813%')";
			}else{
				$wh.="and (b.noakun like '63%' or b.noakun like '64%')";
			}
			$optaruskas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		}else{
			$wh.="and b.noakun = '".$param['akun']."'";
		}
		$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		
		$str = "select distinct a.noaruskas, a.nama_aruskas, substr(b.noakun,1,2) as noakun from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' ".$wh." order by substr(b.noakun,1,2), a.noaruskas asc"; //exit("error".$str);
		$res=fetchdata($str);
		if(count($res)==0){
			if($param['akun']!=''){
				$xx=" nomor akun = ".$param['akun']."";
			}
			//exit("Error : Arus kas untuk akun ".$param['kdbudget']." ".$xx." belum ada.");
		}
		
		foreach($res as $bar){
			$d=substr($bar['noakun'],0,2);
			if($d!=$n){			
				$optaruskas.="<optgroup label='".$d." - ".$optakun[$d]."'>";
			}
			$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
			$n=$d;
			if($d!=$n){			
				$optaruskas.="</optgroup>";
			}
		}
		
		echo $optaruskas;
	break;
	case'getakun':
		$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
		
		$wh="and a.noaruskas = '".$param['aruskas']."'";
		
		$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($param['kodeorg'],0,4)."'");
			
		if($tipeorg[substr($param['kodeorg'],0,4)]=='PABRIK'){
			$wh.="and (b.noakun like '63%' or b.noakun like '64%')";
		}elseif($tipeorg[substr($param['kodeorg'],0,4)]=='BULKING'){
			$wh.="and b.noakun like '81%'";
		}else{
			$wh.="and (b.noakun like '63%' or b.noakun like '64%')";
		}
		
		
		$optaruskas="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct noakun, tipetransaksi from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' ".$wh." order by a.noaruskas asc, tipetransaksi asc"; #exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$d=substr($bar['noakun'],0,3);
			if($d!=$n){			
				$optaruskas.="<optgroup label='".$d." - ".$optakun[$d]."'>";
			}
			$optaruskas.="<option value=".$bar['noakun'].">".$bar['noakun']." - ".$optakun[$bar['noakun']]." (".$bar['tipetransaksi'].")</option>";
			$n=$d;
			if($d!=$n){			
				$optaruskas.="</optgroup>";
			}
		}
		
		echo $optaruskas;
	break;
	
	case'gethargavhc':
		if($param['kodevhc']==''){
		   exit("Warning : Kode kendaraan wajib diisi.");
		}
		$str="select distinct rpperjam from ".$dbname.".bgt_biaya_ken_per_jam where tahunbudget='".$param['tahun']."' and kodevhc='".$param['kodevhc']."'";
		$res=fetchdata($str);
		if(count($res)>0){
			if($res[0]['rpperjam']==''){
				exit("Warning : Data rupiah / jam kendaraan belum ada, silahkan cek kembali");
			}else{
				$rp=$res[0]['rpperjam'];
			}
		}else{
			exit("Error : Budget Kendaraan belum diinput.");
		}
		
		$jnsvhc=makeOption($dbname,'vhc_5master','kodevhc,jenisvhc',"kodevhc='".$param['kodevhc']."'");
		$kelvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,kelompokvhc',"jenisvhc='".$jnsvhc[$param['kodevhc']]."'");
		
		if($kelvhc[$jnsvhc[$param['kodevhc']]]=='KD'){
			$sat="KM";
		}else{
			$sat="HM";
		}
			
		echo $rp."####".$sat;
	break;
	
	case'simpanheader':
		cekheader($param);
		
		$str="select distinct tutup from ".$dbname.".bgt_budget where 1=1 ".$whr." and kodeorg like '".$param['station']."%'";
		$res=fetchdata($str);
		if($res[0]['tutup']>0){
		   exit("Warning : Budget ".$param['tahun']." sudah ditutup.");
		}
		
		$str="select distinct * from ".$dbname.".bgt_hk where tahunbudget='".$param['tahun']."' and unit = '".substr($param['kodeorg'],0,4)."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$thrlb    =$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
			$thke     =$bar['harisetahun']-$thrlb;
			$tsim     =$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
			$tothke   =$thke-($bar['jlhcuti']+$tsim);
			$hkefektip=$tothke;
		}
		
		$optvhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct a.kodetraksi ,a.kodevhc from ".$dbname.".bgt_biaya_jam_ken_vs_alokasi a left join ".$dbname.".bgt_vhc_jam b on a.tahunbudget=b.tahunbudget and a.kodevhc=b.kodevhc where a.tahunbudget='".$param['tahun']."' and b.unitalokasi='".$param['kodeorg']."' order by kodevhc asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar['kodevhc']."'");
			$detvhc=makeOption($dbname,'vhc_5master','kodevhc,detailvhc',"kodevhc='".$bar['kodevhc']."'");
			$nopol="";
			if($optnopol[$bar['kodevhc']]!=''){
				$nopol=" - ".$optnopol[$bar['kodevhc']];
			}
			$det="";
			if($detvhc[$bar['kodevhc']]!=''){
				$det=" - ".$detvhc[$bar['kodevhc']];
			}
			
			$d=$bar['kodetraksi'];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodetraksi']."'");
				$optvhc.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			
			$optvhc.="<option value='".$bar['kodevhc']."'>".$bar['kodevhc']."".$nopol."".$det."</option>";
			$n=$d;
			if($d!=$n){			
				$optvhc.="</optgroup>";
			}
		}
		
		$optupah="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select distinct golongan from ".$dbname.".bgt_upah where kodeorg='".$param['kodeorg']."' and tahunbudget='".$param['tahun']."' and jumlah>0";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optupah.="<option value='".$bar['golongan']."'>".strtoupper($nmkode[$bar['golongan']])."</option>";
		}
		
		/* $str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' order by a.noaruskas asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
		} */
		echo $hkefektip."###".$optvhc."###".$optupah;
	
	break;
	
	case'del':
		try{
		$owlPDO->beginTransaction();
			$wh="";
			$wh.=" and tipebudget = 'MILL' and kodebudget!='UMUM' and pta='BGT'";
			if(strlen($param['station'])>4){
				$wh.=" and kodeorg like '".$param['station']."%'";				
			}else{
				$wh.=" and kodeorg = '".$param['station']."'";				
			}
			
			
			$str="delete from ".$dbname.".bgt_budget  where tahunbudget='".$param['tahun']."' ".$wh."";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'delrekapmesin':
		try{
		$owlPDO->beginTransaction();
			$wh="";
			$wh.=" and tipebudget = 'MILL' and kodebudget!='UMUM' and pta='BGT'";
			
			$str="delete from ".$dbname.".bgt_budget  where tahunbudget='".$param['tahun']."' and kodeorg = '".$param['mesin']."' ".$wh.""; 
			$owlPDO->exec($str);
			
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'posting':
		try{
		$owlPDO->beginTransaction();
			
			$where=" and tipebudget = 'MILL' and kodebudget!='UMUM' and pta='BGT'";
			
			$str="select * from ".$dbname.".bgt_budget where 1=1 ".$where." and tahunbudget='".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%'  and kodeorg like '".$param['station']."%' and kunci not in (select kunci from ".$dbname.".bgt_distribusi)";
			$res=fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Masih ada data yang belum di sebaran.");
			}
		
			$str = "update " . $dbname . ".bgt_budget set tutup='1' where 1=1 ".$where." and tahunbudget='".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%' and kodeorg like '".$param['station']."%'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'unposting':
		try{
		$owlPDO->beginTransaction();
			
			$where=" and tipebudget = 'MILL' and kodebudget!='UMUM' and pta='BGT'";
			$str = "update " . $dbname . ".bgt_budget set tutup='0' where 1=1 ".$where." and tahunbudget='".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%' and kodeorg like '".$param['station']."%'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
	case'getstation':
		$optorg="<option value=''>".$param['bahasa']."</option>";
		$where="";
		if($param['kodeorg']!=''){
			$where.=" and kodeorganisasi like '".$param['kodeorg']."%'";
		}
		
		$str="select * from ".$dbname.".organisasi where 1=1 ".$where." and tipe='STATION' order by kodeorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$induk[$bar['kodeorganisasi']];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optorg.="</optgroup>";
			}
		}
		
		$optmesin="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where 1=1 ".$where." and tipe='STENGINE' order by kodeorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$induk[$bar['kodeorganisasi']];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optmesin.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optmesin.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optmesin.="</optgroup>";
			}
		}


		echo $optorg."####".$optmesin;
	break;
	case'getmesin':
		$where="";
		if($param['station']!=''){
			$where.=" and kodeorganisasi like '".$param['station']."%'";
		}
		
		
		$optmesin="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where 1=1 ".$where." and tipe='STENGINE' order by kodeorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar['kodeorganisasi']."'");
			$d=$induk[$bar['kodeorganisasi']];
			if($d!=$n){			
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
				$optmesin.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
			}
			$optmesin.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			$n=$d;
			if($d!=$n){			
				$optmesin.="</optgroup>";
			}
		}


		echo $optmesin;
	break;
	
	
	case'showposting':
		$where = "";
		if($param['tahun']!=''){
			$where.=" and a.tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and a.kodeorg like '".$param['kodeorg']."%'";
		}
		$where.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
        $tab = "";
		$colspan=12;
		
		$data=array();
		$str = "SELECT tahunbudget, substr(kodeorg,1,4) as unit, tipebudget, kodebudget, sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget a where substr(a.kodeorg,1,4) in (".getOrgDetail(25).") ".$where." group by tahunbudget, substr(kodeorg,1,4), tipebudget, kodebudget";
		$res=fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){				
				$data[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]['sdm']+=$bar['jumlah'];
			}
			if(substr($bar['kodebudget'],0,2)=='M-'){				
				$data[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]['mat']+=$bar['jumlah'];
			}
			if($bar['kodebudget']=='MAIN' or $bar['kodebudget']=='SERVICE' or $bar['kodebudget']=='PKSM'){
				$data[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]['main']+=$bar['jumlah'];
			}
			if(substr($bar['kodebudget'],0,3)=='VHC'){				
				$data[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]['vhc']+=$bar['jumlah'];
			}
			if(substr($bar['kodebudget'],0,7)=='KONTRAK'){				
				$data[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]['kont']+=$bar['jumlah'];
			}
			if(substr($bar['kodebudget'],0,7)=='LAIN'){				
				$data[$bar['tahunbudget']][$bar['unit']][$bar['tipebudget']]['lain']+=$bar['jumlah'];
			}
		}
		
		$prd=array();
		$str = "SELECT tahunbudget, millcode, sum(kgolah) as tbs,sum(kgcpo) as cpo,sum(kgkernel) as ker FROM " . $dbname . ".bgt_produksi_pks_vw where millcode in (".getOrgDetail(25).") group by tahunbudget, millcode";
		$res=fetchdata($str);
		foreach($res as $bar){
			$prd[$bar['tahunbudget']][$bar['millcode']]['tbs']+=$bar['tbs'];
			$prd[$bar['tahunbudget']][$bar['millcode']]['cpo']+=$bar['cpo'];
			$prd[$bar['tahunbudget']][$bar['millcode']]['ker']+=$bar['ker'];
		}
		
		$str="select a.*,substr(kodeorg,1,4) as kdunit, sum(a.rupiah) as rupiah from ".$dbname.".bgt_budget a where substr(a.kodeorg,1,4) in (".getOrgDetail(25).") ".$where." group by a.tahunbudget, substr(kodeorg,1,4) order by a.tahunbudget desc,a.kodeorg asc";
		$res=fetchdata($str);
		if(count($res) > 0){
			$no=0;
			foreach($res as $bar){					
				$no++;
				$tab.="<tr class='rowcontent' style=height:25px>";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td align=center>".$bar['tahunbudget']."</td>";
				$tab.="<td align=left>".substr($bar['kodeorg'],0,4)." - ".$nmorg[substr($bar['kodeorg'],0,4)]."</td>";
				/* #SDM
				$sdm = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and substr(kodeorg,1,4)='".$bar['kdunit']."' and tipebudget='".$bar['tipebudget']."' and (kodebudget like 'EXPL%' or kodebudget like 'SDM%')";
				$ressdm = fetchData($sdm);
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ressdm[0]['jumlah'])."</td>";
				#Material
				$mat = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and substr(kodeorg,1,4)='".$bar['kdunit']."' and tipebudget='".$bar['tipebudget']."' and kodebudget like 'M-%'";
				$resmat = fetchData($mat);
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($resmat[0]['jumlah'])."</td>";
				#main
				$tool = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and substr(kodeorg,1,4)='".$bar['kdunit']."' and tipebudget='".$bar['tipebudget']."' and kodebudget in ('MAIN','SERVICE')";
				$restool = fetchData($tool);
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($restool[0]['jumlah'])."</td>";
				#vhc
				$vhc = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and substr(kodeorg,1,4)='".$bar['kdunit']."' and tipebudget='".$bar['tipebudget']."' and kodebudget like 'VHC%'";
				$resvhc = fetchData($vhc);
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($resvhc[0]['jumlah'])."</td>";
				
				$ttl=$resvhc[0]['jumlah']+$restool[0]['jumlah']+$resmat[0]['jumlah']+$ressdm[0]['jumlah'];
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl)."</td>";
				 */
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($prd[$bar['tahunbudget']][$bar['kdunit']]['tbs'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($prd[$bar['tahunbudget']][$bar['kdunit']]['cpo'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($prd[$bar['tahunbudget']][$bar['kdunit']]['ker'])."</td>";
				
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['sdm'])."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['mat'])."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['main'])."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['kont'])."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['vhc'])."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['lain'])."</td>";
				
				
				
				$ttl=$data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['sdm']+$data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['mat']+$data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['main']+$data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['vhc']+$data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['kont']+$data[$bar['tahunbudget']][$bar['kdunit']][$bar['tipebudget']]['lain'];
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl)."</td>";
				
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl/$prd[$bar['tahunbudget']][$bar['kdunit']]['tbs'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl/$prd[$bar['tahunbudget']][$bar['kdunit']]['cpo'])."</td>";
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl/$prd[$bar['tahunbudget']][$bar['kdunit']]['ker'])."</td>";
				
				
				if($bar['tutup']==0){
					$tab.="<td align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$bar['tahunbudget']."','".$bar['kdunit']."','');\" title='Posting'></td>";
				}else{
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$icon="images/icons/04/16/04.png";
						$title="Unclose / Unposting";
						$unpost=" onclick=\"unposting('".$bar['tahunbudget']."','".$bar['kdunit']."','');\" ";
					}else {
						$icon="images/icons/04/16/02.png";
						$title="Closed / Posted";
						$unpost='';
					}
					$tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
				}
				
				$tab.="</tr>";
			
			}
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>Data tidak ditemukan.</td></tr>";
		}
		
        echo $tab;
	break;
	
    case'loaddata':
        $tab = "";
		if($param['jenis']=='excel'){
			$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=1>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center style='width:50px'>".$_SESSION['lang']['budgetyear']."</th>
					<th align=center>".$_SESSION['lang']['kodeorg']."</th>
					<th align=center>".$_SESSION['lang']['station']."</th>
					<th align=center>".$_SESSION['lang']['sdm']."</th>
					<th align=center>".$_SESSION['lang']['material']."</th>
					<th align=center>".$_SESSION['lang']['pemeliharaan']."</th>
					<th align=center>".$_SESSION['lang']['kontrak']."</th>
					<th align=center>".$_SESSION['lang']['kndran']."</th>
					<th align=center>".$_SESSION['lang']['lain']."</th>
					<th align=center>".$_SESSION['lang']['total']."</th>
				</tr>
			</thead><tbody>";
		}
		
        $where = "";
		if($param['tahun']!=''){
			$where.=" and a.tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and a.kodeorg like '".$param['kodeorg']."%'";
		}
		if($param['station']!=''){
			$where.=" and a.kodeorg like '".$param['station']."%'";
		}
		
		$where.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";

		$orgDetails = "''";
		if (!empty(getOrgDetail(25))) {
			$orgDetails = getOrgDetail(25);
			$where.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(25).")";
		}
		
		
		
		$limit= 20;
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 21;
		
        $sql = "select tahunbudget from ".$dbname.".bgt_budget a where 1=1 ".$where." group by substr(kodeorg,1,6)";
        $res = fetchdata($sql);
        $jlhbrs = count($res);

		
		$rowspan="";
		if($param['jenis']!='excel'){$lmt="limit " . $offset . "," . $limit . "";}
		
		$data=array();
		$str = "SELECT tahunbudget, substr(kodeorg,1,6) as station, tipebudget, kodebudget, sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget a where 1=1 ".$where." group by tahunbudget, substr(kodeorg,1,6), tipebudget, kodebudget";
		$res=fetchdata($str);
		foreach($res as $bar){
			if(substr($bar['kodebudget'],0,3)=='SDM' or substr($bar['kodebudget'],0,4)=='EXPL'){				
				$data[$bar['tahunbudget']][$bar['station']][$bar['tipebudget']]['sdm']+=$bar['jumlah'];
			}elseif(substr($bar['kodebudget'],0,2)=='M-'){				
				$data[$bar['tahunbudget']][$bar['station']][$bar['tipebudget']]['mat']+=$bar['jumlah'];
			}elseif($bar['kodebudget']=='MAIN' or $bar['kodebudget']=='SERVICE' or $bar['kodebudget']=='PKSM'){
				$data[$bar['tahunbudget']][$bar['station']][$bar['tipebudget']]['main']+=$bar['jumlah'];
			}elseif(substr($bar['kodebudget'],0,3)=='VHC'){				
				$data[$bar['tahunbudget']][$bar['station']][$bar['tipebudget']]['vhc']+=$bar['jumlah'];
			}elseif(substr($bar['kodebudget'],0,7)=='KONTRAK'){				
				$data[$bar['tahunbudget']][$bar['station']][$bar['tipebudget']]['kont']+=$bar['jumlah'];
			}else{
			// if(substr($bar['kodebudget'],0,4)=='LAIN'){
				$data[$bar['tahunbudget']][$bar['station']][$bar['tipebudget']]['lain']+=$bar['jumlah'];
			}
		}
		
		$prd=array();
		#millcode in (".getOrgDetail(25).")
		$str = "SELECT tahunbudget, millcode, sum(kgolah) as tbs,sum(kgcpo) as cpo,sum(kgkernel) as ker FROM " . $dbname . ".bgt_produksi_pks_vw where millcode in (".$orgDetails	.") group by tahunbudget, millcode";
		$res=fetchdata($str);
		foreach($res as $bar){
			$prd[$bar['tahunbudget']][$bar['millcode']]['tbs']+=$bar['tbs'];
			$prd[$bar['tahunbudget']][$bar['millcode']]['cpo']+=$bar['cpo'];
			$prd[$bar['tahunbudget']][$bar['millcode']]['ker']+=$bar['ker'];
		}
		
		$str = "SELECT tahunbudget, millcode, sum(kgolah) as tbs,sum(kgcpo) as cpo,sum(kgkernel) as ker FROM " . $dbname . ".bgt_produksi_bulk where millcode in (".$orgDetails	.") group by tahunbudget, millcode";
		$res=fetchdata($str);
		foreach($res as $bar){
			$prd[$bar['tahunbudget']][$bar['millcode']]['tbs']+=$bar['tbs'];
			$prd[$bar['tahunbudget']][$bar['millcode']]['cpo']+=$bar['cpo'];
			$prd[$bar['tahunbudget']][$bar['millcode']]['ker']+=$bar['ker'];
		}
		
		$str="select substr(a.kodeorg,1,4) as kodeunit,substr(a.kodeorg,1,6) as station,a.* from ".$dbname.".bgt_budget a where 1=1 ".$where." group by substr(kodeorg,1,6) order by a.tahunbudget desc,a.kodeorg asc ".$lmt."";
		$res=fetchdata($str);
		if(count($res)>10000){
			exit("Warning : Data terlalu banyak, silahkan filter terlebih dahulu.");
		}
		
		if(count($res) > 0){
			foreach($res as $val){
				$no++;
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;'>".$no."</td>";
				$tab.="<td style='text-align:center;'>".$val['tahunbudget']."</td>";
				#$tab.="<td style='text-align:left;'>".$val['kodeunit']." - ".$nmorg[$val['kodeunit']]."</td>";
				$tab.="<td style='text-align:left;'>".$val['kodeunit']."</td>";
				$tab.="<td style='text-align:left;'>".$val['station']." - ".$nmorg[$val['station']]."</td>";
				
				$getdt="'".$val['tahunbudget']."','".$val['kodeunit']."','".$val['station']."',''";
				
				$tab.="<td style='text-align:right;'>".@hidezerodecimal($prd[$val['tahunbudget']][$val['kodeunit']]['tbs'])."</td>";
				$tab.="<td style='text-align:right;'>".@hidezerodecimal($prd[$val['tahunbudget']][$val['kodeunit']]['cpo'])."</td>";
				$tab.="<td style='text-align:right;'>".@hidezerodecimal($prd[$val['tahunbudget']][$val['kodeunit']]['ker'])."</td>";
				
				
				$tab.="<td style='text-align:right;color:blue;cursor:pointer;' onclick=\"getdatadetail('sdm',".$getdt.")\">".@hidezerodecimal($data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['sdm'])."</td>";
				
				$tab.="<td style='text-align:right;color:blue;cursor:pointer;' onclick=\"getdatadetail('mat',".$getdt.")\">".@hidezerodecimal($data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['mat'])."</td>";
				
				$tab.="<td style='text-align:right;color:blue;cursor:pointer;' onclick=\"getdatadetail('main',".$getdt.")\">".@hidezerodecimal($data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['main'])."</td>";
				
				$tab.="<td style='text-align:right;color:blue;cursor:pointer;' onclick=\"getdatadetail('kont',".$getdt.")\">".@hidezerodecimal($data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['kont'])."</td>";
				
				$tab.="<td style='text-align:right;color:blue;cursor:pointer;' onclick=\"getdatadetail('vhc',".$getdt.")\">".@hidezerodecimal($data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['vhc'])."</td>";
				
				$tab.="<td style='text-align:right;color:blue;cursor:pointer;' onclick=\"getdatadetail('lain',".$getdt.")\">".@hidezerodecimal($data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['lain'])."</td>";
				
				
				$ttl=$data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['sdm']+$data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['mat']+$data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['main']+$data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['vhc']+$data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['kont']+$data[$val['tahunbudget']][$val['station']][$val['tipebudget']]['lain'];
				
				$tab.="<td style='text-align:right;'>".@hidezerodecimal($ttl)."</td>";
				
				$tab.="<td style='text-align:right;'>".@hidezerodecimal($ttl/$prd[$val['tahunbudget']][$val['kodeunit']]['tbs'])."</td>";
				$tab.="<td style='text-align:right;'>".@hidezerodecimal($ttl/$prd[$val['tahunbudget']][$val['kodeunit']]['cpo'])."</td>";
				$tab.="<td style='text-align:right;'>".@hidezerodecimal($ttl/$prd[$val['tahunbudget']][$val['kodeunit']]['ker'])."</td>";
				
				if($val['tutup']=='0'){
					$tab.="<td align=center style='' width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editdetail('".$val['tahunbudget']."','".$val['kodeunit']."','".$val['station']."');\" ></td>";
					
					$tab.="<td align=center style='' width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$val['tahunbudget']."','".$val['station']."');\" title='Delete'></td>";
					
					$tab.="<td align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$val['tahunbudget']."','".$val['kodeunit']."','".$val['station']."');\" title='Posting'></td>";
				}else{
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$icon="images/icons/04/16/04.png";
						$title="Unclose / Unposting";
						$unpost=" onclick=\"unposting('".$val['tahunbudget']."','".$val['kodeunit']."','".$val['station']."');\" ";
					}else {
						$icon="images/icons/04/16/02.png";
						$title="Closed / Posted";
						$unpost='';
					}
					$tab.="<td align=center width=25px></td>";
					$tab.="<td align=center width=25px></td>";
					$tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
				}
				$tab.="<td align=center style='' width=25px><img src=images/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview'  onclick=\"preview('".$val['tahunbudget']."','".$val['station']."','html');\" ></td>";

				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
		
		## PAGING
		$foot=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
		if($param['jenis']=='excel'){
			$tab.="</tbody></table>";
			$nop = "bgt_prd_".$param['tahun'].".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("bgt_prd_".$param['tahun'], $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{			
			echo $tab . "####" . $foot;
		}
	break;
	case'showsebaran':
        $tab = "";
		$bulan = range(1,12);
		
        $where = "";
		if($param['tahun']!=''){
			$where.=" and a.tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and a.kodeorg like '".$param['kodeorg']."%'";
		}
		if($param['station']!=''){
			$where.=" and a.kodeorg like '".$param['station']."%'";
		}
		if($param['sebaran']=='1'){
			$where.=" and c.kunci IS NOT NULL";
		}
		if($param['sebaran']=='2'){
			$where.=" and c.kunci IS NULL";
		}
		$where.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
		
		if($param['jlhbaris']>'5000'){
			exit("Warning : Jumlah baris maksimal 5000");
		}
		
		if($param['jlhbaris']=='' or $param['jlhbaris']=='0'){			
			$limit= 50;
		}else{
			$limit= $param['jlhbaris'];
		}
		
		if($param['tampilkan']=='1'){
			$group="group by tahunbudget,substr(kodeorg,1,6)";
		}elseif($param['tampilkan']=='3'){
			$group="group by tahunbudget,substr(kodeorg,1,6), kodebudget, kegiatan, noakun, kodebarang, kodevhc";
		}else{
			$group="group by tahunbudget,kodeorg, kodebudget, kegiatan, noakun, kodebarang, kodevhc";
		}
		
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		if($param['tampilkan']=='2'){
			$colspan   = 22;
		}elseif($param['tampilkan']=='3'){
			$colspan   = 21;
		}else{			
			$colspan   = 18;
		}
		
        $sql = "select count(*) from ".$dbname.".bgt_budget a left join ".$dbname.".bgt_distribusi c on a.kunci=c.kunci  where substr(a.kodeorg,1,4) in (".getOrgDetail(2).") ".$where." ".$group."";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		
		$rowspan="";
		$lmt="limit " . $offset . "," . $limit . "";
		
		$str="select c.kunci as kuncisebar,sum(c.rp01) as rp01,sum(c.rp02) as rp02,sum(c.rp03) as rp03,sum(c.rp04) as rp04,sum(c.rp05) as rp05,sum(c.rp06) as rp06,sum(c.rp07) as rp07,sum(c.rp08) as rp08,sum(c.rp09) as rp09,sum(c.rp10) as rp10,sum(c.rp11) as rp11,sum(c.rp12) as rp12, substr(a.kodeorg,1,4) as kodeunit,substr(a.kodeorg,1,6) as station,a.*,sum(a.rupiah) as rupiah 
		from ".$dbname.".bgt_budget a 
		left join ".$dbname.".bgt_distribusi c on a.kunci=c.kunci  
		where substr(a.kodeorg,1,4) in (".getOrgDetail(2).") ".$where." ".$group."
		order by a.tahunbudget desc,left(a.kodeorg,6) asc, FIELD(left(a.kodebudget,3),'SDM','M-3','TOO','SER','LAI','KON','VHC'), a.kodebarang asc ".$lmt."";
		$res=fetchdata($str);
		if(count($res)>10000){
			exit("Warning : Data terlalu banyak, silahkan filter terlebih dahulu.");
		}
		$awal=0;
		if(count($res) > 0){
			$tab.="<tr class='rowcontent'>
					<td colspan=".$colspan." style='text-align:left'>
						<button class=mybutton id=btnprev onclick=sebarkan('".$awal."','".$no."','".$param['tampilkan']."')>" . $_SESSION['lang']['sebaran'] . " " . $_SESSION['lang']['all'] . "</button></td></tr>";
			foreach($res as $val){
				$no++;
				if($awal==0){
					$awal=$no;
				}
				
				$check="";
				$tab.="<tr class='rowcontent' style='height:25px' id=rowsebar".$no.">";
				if($param['tampilkan']=='1'){
					$tab.="<td width=25px align=center>
							<input id=chkboxsebar".$no." type=checkbox ".$check." onclick=sebartt('".$no."'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				}elseif($param['tampilkan']=='2'){					
					$tab.="<td width=25px align=center>
							<input id=chkboxsebar".$no." type=checkbox ".$check." onclick=sebardetail('".$no."','".$no."'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				}elseif($param['tampilkan']=='3'){					
					$tab.="<td width=25px align=center>
							<input id=chkboxsebar".$no." type=checkbox ".$check." onclick=sebarperstation('".$no."','".$no."'); title=\"Sebarkan sesuai proporsi diatas\">
						</td>";
				}
				$tab.="<td hidden id=index".$no.">".$val['kunci']."</td>";
				$tab.="<td style='text-align:center;vertical-align:middle;'>".$no."</td>";
				$tab.="<td style='text-align:center;vertical-align:middle;' id=tahun".$no.">".$val['tahunbudget']."</td>";
				$tab.="<td style='text-align:left;vertical-align:middle;'>".$val['kodeunit']." - ".$nmorg[$val['kodeunit']]."</td>";
				$tab.="<td style='text-align:center;vertical-align:middle;' hidden id=station".$no.">".$val['station']."</td>";
				$tab.="<td style='text-align:left;vertical-align:middle;'>".$nmorg[$val['station']]."</td>";
				if($param['tampilkan']=='2'){
					$tab.="<td style='text-align:left;vertical-align:middle;'>".$nmorg[$val['kodeorg']]."</td>";
				}
				if($param['tampilkan']!='1'){
					$tab.="<td style='text-align:left;vertical-align:middle;' id='kodebudget".$no."'>".$val['kodebudget']."</td>";
					$tab.="<td style='text-align:left;vertical-align:middle;'>".getNamaBrg($val['kodebarang'])."</td>";
					$tab.="<td hidden id='kodebarang".$no."'>".$val['kodebarang']."</td>";
					$tab.="<td style='text-align:left;vertical-align:middle;' id='kodevhc".$no."'>".$val['kodevhc']."</td>";
				}
				$tab.="<td style='text-align:right;vertical-align:middle;'>".hidezerodecimal($val['rupiah'])."</td>";
				foreach($bulan as $bln){					
					$tab.="<td style='text-align:right;vertical-align:middle;'>".hidezerodecimal($val['rp'.addZero($bln,2)])."</td>";
				}

				$tab.="</tr>";
			}
			$tab.="<tr class='rowcontent' style=display:none>
						<td colspan=".$colspan." style='text-align:left'>
						<input id=awalsebar value='".$awal."'>
						<input id=akhirsebar value='".$no."'>
							<button class=mybutton id=btnprev onclick=sebarkan('".$awal."','".$no."','".$param['tampilkan']."')>" . $_SESSION['lang']['sebaran'] . " " . $_SESSION['lang']['all'] . "</button>
						</tr>";
			
		}else{
			$tab.="<tr class='rowcontent'><td colspan=".$colspan." style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}
		
		## PAGING
		$foot=createpagingsebar($jlhbrs,$limit,$page,$colspan,'showsebaran','getPageSbr');
		
		
		echo $tab . "####" . $foot;
		// if($param['jenis']=='excel'){
			// $tab.="</tbody></table>";
			// $nop = "bgt_prd_".$param['tahun'].".xls";
			// $xls = new HtmlExcel();
			// $xls->setCss($css);
			// $xls->addSheet("bgt_prd_".$param['tahun'], $tab);
			// $xls->headers($nop);
			// echo $xls->buildFile();
		// }else{			
		// }
	break;
	case'rekappermesin':
		$tab.="
			<table class='sortable' cellspacing=1 cellpadding=5 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['mesin']."</th>
					<th align=center>".$_SESSION['lang']['sdm']."</th>
					<th align=center>".$_SESSION['lang']['material']."</th>
					<th align=center>".$_SESSION['lang']['pemeliharaan']."</th>
					<th align=center>".$_SESSION['lang']['kontrak']."</th>
					<th align=center>".$_SESSION['lang']['kndran']."</th>
					<th align=center>".$_SESSION['lang']['lain']."</th>
					<th align=center>".$_SESSION['lang']['total']."</th>
					<th align=center>Action</th>
				</tr>
			</thead><tbody>";
			$wh=" and a.kodeorg like '".$param['station']."%'";
			$wh.=" and a.tahunbudget = '".$param['tahun']."'";
			$wh.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
			
			$str="select substr(a.kodeorg,1,6) as station,a.* from ".$dbname.".bgt_budget a where 1=1 ".$wh." group by kodeorg order by kodeorg asc";
			$res=fetchdata($str);
			foreach($res as $val){
				$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$val['noakun']."'");
				$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$val['kegiatan']."'");
				
				$no++;
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;vertical-align:middle;'>".$no."</td>";
				$tab.="<td style='text-align:left;vertical-align:middle;'>".$val['kodeorg']." - ".$nmorg[$val['kodeorg']]."</td>";
				
				$getdt="'".$val['tahunbudget']."','".$val['kodeunit']."','".$val['station']."','".$val['kodeorg']."'";
				
				#SDM
				$sdm = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and kodeorg='".$val['kodeorg']."' and tipebudget='".$val['tipebudget']."' and (kodebudget like 'EXPL%' or kodebudget like 'SDM%')";
				$ressdm = fetchData($sdm);
				$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' onclick=\"getdatadetail('sdm',".$getdt.")\">".@hidezerodecimal($ressdm[0]['jumlah'])."</td>";
				
				#Material
				$mat = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and kodeorg='".$val['kodeorg']."' and tipebudget='".$val['tipebudget']."' and kodebudget like 'M-%'";
				$resmat = fetchData($mat);
				$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' onclick=\"getdatadetail('mat',".$getdt.")\">".@hidezerodecimal($resmat[0]['jumlah'])."</td>";
				
				#main
				$main = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and kodeorg='".$val['kodeorg']."' and tipebudget='".$val['tipebudget']."' and kodebudget in ('MAIN','SERVICE')";
				$resmain = fetchData($main);
				$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' onclick=\"getdatadetail('main',".$getdt.")\">".@hidezerodecimal($resmain[0]['jumlah'])."</td>";
				
				#kont
				$kont = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and kodeorg='".$val['kodeorg']."' and tipebudget='".$val['tipebudget']."' and kodebudget in ('KONTRAK')";
				$reskont = fetchData($kont);
				$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' onclick=\"getdatadetail('kont',".$getdt.")\">".@hidezerodecimal($reskont[0]['jumlah'])."</td>";
				
				#vhc
				$vhc = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and kodeorg='".$val['kodeorg']."' and tipebudget='".$val['tipebudget']."' and kodebudget like 'VHC%'";
				$resvhc = fetchData($vhc);
				$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' onclick=\"getdatadetail('vhc',".$getdt.")\">".@hidezerodecimal($resvhc[0]['jumlah'])."</td>";
				
				#lain
				$lain = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$val['tahunbudget']."' and kodeorg='".$val['kodeorg']."' and tipebudget='".$val['tipebudget']."' and kodebudget in ('LAIN','TBS','CPO','KER')";
				$reslain = fetchData($lain);
				$tab.="<td style='text-align:right;vertical-align:middle;color:blue;cursor:pointer;' onclick=\"getdatadetail('lain',".$getdt.")\">".@hidezerodecimal($reslain[0]['jumlah'])."</td>";
				
				$ttl=$resvhc[0]['jumlah']+$resmain[0]['jumlah']+$resmat[0]['jumlah']+$ressdm[0]['jumlah'];
				$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttl)."</td>";
				
				if($param['jenis']!='excel' and $val['tutup']!='1'){					
					$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delrekapmesin('".$val['tahunbudget']."','".$val['kodeorg']."');\" title='Delete'></td>";
				}else{
					$tab.="<td align=center width=25px></td>";
				}
				$ttlvol+=$val['volume'];
				$ttlsdm+=$ressdm[0]['jumlah'];
				$ttlmat+=$resmat[0]['jumlah'];
				$ttlmain+=$resmain[0]['jumlah'];
				$ttlkont+=$reskont[0]['jumlah'];
				$ttlvhc+=$resvhc[0]['jumlah'];
				$ttllain+=$reslain[0]['jumlah'];
				$gttl=$ttlvol+$ttlsdm+$ttlmat+$ttlmain+$ttlvhc+$ttlkont+$ttllain;
				
				$tab.="</tr>";
			}
			
			$tab.="<tr class='rowcontent' style='height:25px'>";
			$tab.="<td style='text-align:center;vertical-align:middle;' colspan=2>TOTAL</td>";
			$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttlsdm,0)."</td>";
			$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttlmat,0)."</td>";
			$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttlmain,0)."</td>";
			$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttlkont,0)."</td>";
			$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttlvhc,0)."</td>";
			$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($ttllain,0)."</td>";
			$tab.="<td style='text-align:right;vertical-align:middle;'>".@hidezerodecimal($gttl,0)."</td>";
			$tab.="<td style='text-align:center;vertical-align:middle;'></td>";
			$tab.="</tr>";
			
		$tab.="</tbody></table>";	
		
		echo $tab;
	break;
	case'loaddatasdm':
		if($param['tipe']!='popup'){			
			//$tab.="<div class='table-scroll'>";
		}
		$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['station']."</th>
					<th align=center>".$_SESSION['lang']['mesin']."</th>
					<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
					<th align=center style='width:55px'>".$_SESSION['lang']['noakun']."</th>
					<th align=center>".$_SESSION['lang']['namaakun']."</th>
					<th align=center>".$_SESSION['lang']['aruskas']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['rp']."</th>
					<th align=center colspan=2>Action</th>
				</tr>
			</thead><tbody>";
			
			$nmkeg = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$param['kegiatan']."'");
			
			if($param['mesin']!=''){
				$style="";
				$stytt="style=cursor:pointer;height:25px;display:none";
				$wh=" and a.kodeorg like '".$param['mesin']."%'";
			}else{				
				$stytt="style=cursor:pointer;height:25px";
				if($param['tipe']!='popup'){					
					$style="style=display:none";
				}
				$wh=" and a.kodeorg like '".$param['station']."%'";
				$wh.=" and substr(a.kodeorg,1,4) = '".$param['kodeorg']."'";
			}
			
			$wh.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
			$wh.=" and a.tahunbudget = '".$param['tahun']."'";
			$wh.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
			$wh.=" and (kodebudget like 'EXPL%' or kodebudget like 'SDM%')";
			
			$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');
			$data=array();
			$str="select a.*, substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." order by kodeorg asc, kodebudget asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['station']][$bar['kodebudget']]=$bar['kodebudget'];
				$dtb[$bar['station']][$bar['kodebudget']]['kas']=$bar['aruskas'];
				$dtb[$bar['station']][$bar['kodebudget']]['satv']=$bar['satuanv'];
				$dtb[$bar['station']][$bar['kodebudget']]['satj']=$bar['satuanj'];
				$dtb[$bar['station']][$bar['kodebudget']]['jlh']+=$bar['jumlah'];
				$dtb[$bar['station']][$bar['kodebudget']]['rp']+=$bar['rupiah'];
				$dtb[$bar['station']][$bar['kodebudget']]['acc']=$bar['noakun'];
			}
			if($_SESSION['standard']['userid']=='0000000007'){
				// echo $str;
			}
			
			if(count($res)>0){
				$no=0;
				foreach($data as $stat => $vkdbgt){
					foreach($vkdbgt as $kdbgt){
						$str="select a.*,substr(kodeorg,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." and kodebudget='".$kdbgt."'order by kodeorg asc";
						$res=fetchdata($str);
						$row=0;
						foreach($res as $bar){
							$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['aruskas']."'");
							$row++;
							$no++;
							
							$tab.="<tr class='rowcontent' id=row_".$no." ".$style.">";
							$tab.="<td style='text-align:right;'>".$no."</td>";
							$tab.="<td style='text-align:left;'>".$nmorg[$stat]."</td>";
							$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
							$tab.="<td style='text-align:left;'>".$nmkode[$bar['kodebudget']]."</td>";
							$tab.="<td style='text-align:center;'>".$bar['noakun']."</td>";
							$tab.="<td style='text-align:left;'>".$nmakun[$bar['noakun']]."</td>";
							$tab.="<td style='text-align:left;'>".$bar['aruskas']." - ".$nmkas[$bar['aruskas']]."</td>";
							$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['jumlah'],2)."</td>";
							$tab.="<td style='text-align:center;'>".$bar['satuanj']."</td>";
							$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rupiah'])."</td>";
							if($param['jenis']!='excel'){
								if($param['tipe']!='popup'){										
									$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editsdm('".$bar['kunci']."','".$bar['kodeorg']."','".$bar['aruskas']."','".$bar['kodebudget']."','".$bar['jumlah']."','".$bar['rupiah']."');\" ></td>";
									
									$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('".$bar['kunci']."','sdm');\" title='Delete'></td>";
								}else{
									$tab.="<td align=center width=25px></td>";
									$tab.="<td align=center width=25px></td>";
								}		
							}
							
							
							
							$tab.="</tr>";							
							$awal=($no-$row)+1;
						}
						
						$nott++;
						if($param['blok']!=''){
							$isi="<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('".$awal."','".$no."','sdm');\">";
						}else{				
							$isi="<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('".$awal."','".$no."','sdm');\">";
						}
						$click="onclick=\"showhide('".$awal."','".$no."','sdm');\"";
						$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$dtb[$stat][$kdbgt]['kas']."'");
						$tab.="<tr class='rowcontent' ".$stytt.">";
						$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$nott."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmorg[$stat]."</td>";
						$tab.="<td style='text-align:center;background-color:#CAFFF4;' id=plussdm".$awal.">".$isi."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmkode[$kdbgt]."</td>";
						$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt]['acc']."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmakun[$dtb[$stat][$kdbgt]['acc']]."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt]['kas']." - ".$nmkas[$dtb[$stat][$kdbgt]['kas']]."</td>";
						$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt]['jlh'],2)."</td>";
						$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt]['satj']."</td>";
						$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt]['rp'])."</td>";
						if($param['jenis']!='excel'){					
							if($param['tipe']!='popup'){								
								$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editsdm('','','".$dtb[$stat][$kdbgt]['kas']."','".$kdbgt."','".$dtb[$stat][$kdbgt]['jlh']."','".$dtb[$stat][$kdbgt]['rp']."');\" ></td>";
								
								$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('sdm','".$param['tahun']."','".$param['station']."','".$kdbgt."','".$dtb[$stat][$kdbgt]['acc']."','','');\" title='Delete'></td>";
							}else{
								$tab.="<td align=center width=25px></td>";
								$tab.="<td align=center width=25px></td>";
							}		
						}
						
						$ttljlh+=$dtb[$stat][$kdbgt]['jlh'];
						$ttlrp+=$dtb[$stat][$kdbgt]['rp'];
						$tab.="</tr>";
					}
				}
				
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;' colspan=7>TOTAL</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttljlh,2)."</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttlrp,0)."</td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
			
		$tab.="</tbody></table></div>";	
		
		echo $tab;
	break;
	case'loaddatamat':
		if($param['tipe']!='popup'){			
			// $tab.="<div class='table-scroll'>";
		}
		$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['station']."</th>
					<th align=center>".$_SESSION['lang']['mesin']."</th>
					<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
					<th align=center style='width:55px'>".$_SESSION['lang']['noakun']."</th>
					<th align=center>".$_SESSION['lang']['namaakun']."</th>
					<th align=center>".$_SESSION['lang']['aruskas']."</th>
					<th align=center>".$_SESSION['lang']['kodebarang']."</th>
					<th align=center>".$_SESSION['lang']['namabarang']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['rp']."</th>
					<th align=center colspan=2>Action</th>
				</tr>
			</thead><tbody>";
			
			if($param['mesin']!=''){
				$style="";
				$stytt="style=cursor:pointer;height:25px;display:none";
				$wh=" and a.kodeorg like '".$param['mesin']."%'";
			}else{				
				$stytt="style=cursor:pointer;height:25px";
				if($param['tipe']!='popup'){					
					$style="style=display:none";
				}
				$wh=" and a.kodeorg like '".$param['station']."%'";
				$wh.=" and substr(a.kodeorg,1,4) = '".$param['kodeorg']."'";
			}
			$wh.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
			$wh.=" and a.tahunbudget = '".$param['tahun']."'";
			$wh.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
			$wh.=" and substr(kodebudget,1,2)='M-'";
			
			$nmbrg = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
			$data=array();
			$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." order by kodeorg asc, kodebudget asc, kodebarang asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]=$bar['kodebarang'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]['acc']=$bar['noakun'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]['kas']=$bar['aruskas'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]['satj']=$bar['satuanj'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]['jns']=$bar['keterangan'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]['vol']+=$bar['volume'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]['jlh']+=$bar['jumlah'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodebarang']]['rp']+=$bar['rupiah'];
			}
			if(count($res)>0){
				$no=0;
				foreach($data as $stat => $vkdbgt){
					foreach($vkdbgt as $kdbgt => $vbrg){
						foreach($vbrg as $brg){
							$str="select * from ".$dbname.".bgt_budget a  where 1=1 ".$wh." and kodebudget='".$kdbgt."' and kodebarang='".$brg."' order by kodeorg asc";
							$res=fetchdata($str);
							$row=0;
							foreach($res as $bar){
								$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['aruskas']."'");
								$row++;
								$no++;
								
								$tab.="<tr class='rowcontent' id=mat_".$no." ".$style.">";
								$tab.="<td style='text-align:right;'>".$no."</td>";
								$tab.="<td style='text-align:left;'>".$nmorg[$stat]."</td>";
								$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
								$tab.="<td style='text-align:left;'>".$nmkode[$bar['kodebudget']]."</td>";
								$tab.="<td style='text-align:center;'>".$bar['noakun']."</td>";
								$tab.="<td style='text-align:left;'>".$nmakun[$bar['noakun']]."</td>";
								$tab.="<td style='text-align:left;'>".$bar['aruskas']." - ".$nmkas[$bar['aruskas']]."</td>";
								$tab.="<td style='text-align:center;'>".$bar['kodebarang']."</td>";
								$tab.="<td style='text-align:left;'>".$nmbrg[$bar['kodebarang']]."</td>";
								$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['jumlah'],2)."</td>";
								$tab.="<td style='text-align:center;'>".$bar['satuanj']."</td>";
								$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rupiah'])."</td>";
								if($param['jenis']!='excel'){
									if($param['tipe']!='popup'){										
										$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editmat('".$bar['kunci']."','".$bar['kodeorg']."','".$bar['aruskas']."','".$bar['kodebudget']."','".$bar['jumlah']."','".$bar['rupiah']."','".$bar['kodebarang']."','".$nmbrg[$bar['kodebarang']]."','".$bar['satuanj']."','".$bar['keterangan']."','".$bar['noakun']."');\" ></td>";
										
										$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('".$bar['kunci']."','mat');\" title='Delete'></td>";
									}else{
										$tab.="<td align=center width=25px></td>";
										$tab.="<td align=center width=25px></td>";
									}		
								}
								
								
								
								$tab.="</tr>";							
								$awal=($no-$row)+1;
							}
							$nott++;
							if($param['blok']!=''){
								$isi="<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('".$awal."','".$no."','mat');\">";
							}else{				
								$isi="<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('".$awal."','".$no."','mat');\">";
							}
							$click="onclick=\"showhide('".$awal."','".$no."','mat');\"";
							$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$dtb[$stat][$kdbgt][$brg]['kas']."'");
							$tab.="<tr class='rowcontent' ".$stytt.">";
							$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$nott."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmorg[$stat]."</td>";
							$tab.="<td style='text-align:center;background-color:#CAFFF4;' id=plusmat".$awal.">".$isi."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmkode[$kdbgt]."</td>";
							$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$brg]['acc']."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmakun[$dtb[$stat][$kdbgt][$brg]['acc']]."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$brg]['kas']." - ".$nmkas[$dtb[$stat][$kdbgt][$brg]['kas']]."</td>";
							$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$brg."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmbrg[$brg]."</td>";
							$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt][$brg]['jlh'],2)."</td>";
							$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$brg]['satj']."</td>";
							$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt][$brg]['rp'])."</td>";
							if($param['jenis']!='excel'){
								if($param['tipe']!='popup'){										
									$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editmat('','','".$dtb[$stat][$kdbgt][$brg]['kas']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$brg]['jlh']."','".$dtb[$stat][$kdbgt][$brg]['rp']."','".$brg."','".$nmbrg[$brg]."','".$dtb[$stat][$kdbgt][$brg]['satj']."','".$dtb[$stat][$kdbgt][$brg]['jns']."','".$dtb[$stat][$kdbgt][$brg]['acc']."');\" ></td>";
									
									$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('mat','".$param['tahun']."','".$param['station']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$brg]['acc']."','".$brg."','','".$dtb[$stat][$kdbgt][$brg]['jns']."');\" title='Delete'></td>";
								}else{
									$tab.="<td align=center width=25px></td>";
									$tab.="<td align=center width=25px></td>";
								}		
							}
							
							$ttljlh+=$dtb[$stat][$kdbgt][$brg]['jlh'];
							$ttlrp+=$dtb[$stat][$kdbgt][$brg]['rp'];
							$tab.="</tr>";
						}
					}
				}
				
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;' colspan=9>TOTAL</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttljlh,2)."</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttlrp,0)."</td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
			
		$tab.="</tbody></table></div>";	
		
		echo $tab;
	break;
	
	case'loaddatamain':
		if($param['tipe']!='popup'){			
			// $tab.="<div class='table-scroll'>";
		}
		$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['station']."</th>
					<th align=center>".$_SESSION['lang']['mesin']."</th>
					<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
					<th align=center style='width:55px'>".$_SESSION['lang']['noakun']."</th>
					<th align=center>".$_SESSION['lang']['namaakun']."</th>
					<th align=center>".$_SESSION['lang']['aruskas']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['rp']."</th>
					<th align=center colspan=2>Action</th>
				</tr>
			</thead><tbody>";
			
			
			if($param['mesin']!=''){
				$style="";
				$stytt="style=cursor:pointer;height:25px;display:none";
				$wh=" and a.kodeorg like '".$param['mesin']."%'";
			}else{				
				$stytt="style=cursor:pointer;height:25px";
				if($param['tipe']!='popup'){					
					$style="style=display:none";
				}
				$wh=" and a.kodeorg like '".$param['station']."%'";
				$wh.=" and substr(a.kodeorg,1,4) = '".$param['kodeorg']."'";
			}
			$wh.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
			$wh.=" and a.tahunbudget = '".$param['tahun']."'";
			$wh.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
			$wh.=" and kodebudget in ('PKSM','SERVICE','MAIN')";
			
			$data=array();
			$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." order by kodeorg asc, kodebudget asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['station']][$bar['kodebudget']]=$bar['kodebudget'];
				$dtb[$bar['station']][$bar['kodebudget']]['kas']=$bar['aruskas'];
				$dtb[$bar['station']][$bar['kodebudget']]['satv']=$bar['satuanv'];
				$dtb[$bar['station']][$bar['kodebudget']]['satj']=$bar['satuanj'];
				$dtb[$bar['station']][$bar['kodebudget']]['rot']=$bar['rotasi'];
				$dtb[$bar['station']][$bar['kodebudget']]['vol']+=$bar['volume'];
				$dtb[$bar['station']][$bar['kodebudget']]['jlh']+=$bar['jumlah'];
				$dtb[$bar['station']][$bar['kodebudget']]['rp']+=$bar['rupiah'];
				$dtb[$bar['station']][$bar['kodebudget']]['acc']=$bar['noakun'];
				$dtb[$bar['station']][$bar['kodebudget']]['ws']=$bar['kodews'];
			}
			if(count($res)>0){
				$no=0;
				foreach($data as $stat => $vkdbgt){
					foreach($vkdbgt as $kdbgt){
						$str="select * from ".$dbname.".bgt_budget a where 1=1 ".$wh." and kodebudget='".$kdbgt."'order by kodeorg asc";
						$res=fetchdata($str);
						$row=0;
						foreach($res as $bar){
							$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['aruskas']."'");
							$row++;
							$no++;
							
							$tab.="<tr class='rowcontent' id=main_".$no." ".$style.">";
							$tab.="<td style='text-align:right;'>".$no."</td>";
							$tab.="<td style='text-align:left;'>".$nmorg[$stat]."</td>";
							$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
							$tab.="<td style='text-align:left;'>".$nmkode[$bar['kodebudget']]."</td>";
							$tab.="<td style='text-align:center;'>".$bar['noakun']."</td>";
							$tab.="<td style='text-align:left;'>".$nmakun[$bar['noakun']]."</td>";
							$tab.="<td style='text-align:left;'>".$bar['aruskas']." - ".$nmkas[$bar['aruskas']]."</td>";
							$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['jumlah'],2)."</td>";
							$tab.="<td style='text-align:center;'>".$bar['satuanj']."</td>";
							$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rupiah'])."</td>";
							if($param['jenis']!='excel'){
								if($param['tipe']!='popup'){									
									$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editkont('".$bar['kunci']."','".$bar['kodeorg']."','".$bar['aruskas']."','".$bar['kodebudget']."','".$bar['jumlah']."','".$bar['rupiah']."','".$bar['kodews']."');\" ></td>";
									
									$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('".$bar['kunci']."','main');\" title='Delete'></td>";
								}else{
									$tab.="<td align=center width=25px></td>";
									$tab.="<td align=center width=25px></td>";
								}										
							}
							
							$tab.="</tr>";							
							$awal=($no-$row)+1;
						}
						
						$nott++;
						if($param['blok']!=''){
							$isi="<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('".$awal."','".$no."','main');\">";
						}else{				
							$isi="<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('".$awal."','".$no."','main');\">";
						}
						$click="onclick=\"showhide('".$awal."','".$no."','main');\"";
						$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$dtb[$stat][$kdbgt]['kas']."'");
						$tab.="<tr class='rowcontent' ".$stytt.">";
						$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$nott."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmorg[$stat]."</td>";
						$tab.="<td style='text-align:center;background-color:#CAFFF4;' id=plusmain".$awal.">".$isi."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmkode[$kdbgt]."</td>";
						$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt]['acc']."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmakun[$dtb[$stat][$kdbgt]['acc']]."</td>";
						$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt]['kas']." - ".$nmkas[$dtb[$stat][$kdbgt]['kas']]."</td>";
						$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt]['jlh'],2)."</td>";
						$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt]['satj']."</td>";
						$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt]['rp'])."</td>";
						if($param['jenis']!='excel'){	
							if($param['tipe']!='popup'){										
								$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editkont('','','".$dtb[$stat][$kdbgt]['kas']."','".$kdbgt."','".$dtb[$stat][$kdbgt]['jlh']."','".$dtb[$stat][$kdbgt]['rp']."','".$dtb[$stat][$kdbgt]['ws']."');\" ></td>";
								
								$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('main','".$param['tahun']."','".$param['station']."','".$kdbgt."','".$dtb[$stat][$kdbgt]['acc']."','','');\" title='Delete'></td>";
							}else{
								$tab.="<td align=center width=25px></td>";
								$tab.="<td align=center width=25px></td>";
							}								
						}
						
						$ttljlh+=$dtb[$stat][$kdbgt]['jlh'];
						$ttlrp+=$dtb[$stat][$kdbgt]['rp'];
						$tab.="</tr>";
					}
				}
				
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;' colspan=7>TOTAL</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttljlh,2)."</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttlrp,0)."</td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
			
		$tab.="</tbody></table></div>";	
		
		echo $tab;
	break;
	case'loaddatavhc':
		if($param['tipe']!='popup'){			
			// $tab.="<div class='table-scroll'>";
		}
		$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['station']."</th>
					<th align=center>".$_SESSION['lang']['mesin']."</th>
					<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
					<th align=center style='width:55px'>".$_SESSION['lang']['noakun']."</th>
					<th align=center>".$_SESSION['lang']['namaakun']."</th>
					<th align=center>".$_SESSION['lang']['aruskas']."</th>
					<th align=center>".$_SESSION['lang']['kodevhc']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['rp']."</th>
					<th align=center colspan=2>Action</th>
				</tr>
			</thead><tbody>";
			
			if($param['mesin']!=''){
				$style="";
				$stytt="style=cursor:pointer;height:25px;display:none";
				$wh=" and a.kodeorg like '".$param['mesin']."%'";
			}else{				
				$stytt="style=cursor:pointer;height:25px";
				if($param['tipe']!='popup'){					
					$style="style=display:none";
				}
				$wh=" and a.kodeorg like '".$param['station']."%'";
				$wh.=" and substr(a.kodeorg,1,4) = '".$param['kodeorg']."'";
			}
			$wh.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
			$wh.=" and a.tahunbudget = '".$param['tahun']."'";
			$wh.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
			$wh.=" and kodebudget='VHC'";
			
			$optnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol');
			
			$data=array();
			$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." order by kodeorg asc, kodebudget asc, kodevhc asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]=$bar['kodevhc'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['acc']=$bar['noakun'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['kas']=$bar['aruskas'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['satv']=$bar['satuanv'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['satj']=$bar['satuanj'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['rot']=$bar['rotasi'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['vol']+=$bar['volume'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['jlh']+=$bar['jumlah'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['kodevhc']]['rp']+=$bar['rupiah'];
			}
			if(count($res)>0){
				$no=0;
				foreach($data as $stat => $vkdbgt){
					foreach($vkdbgt as $kdbgt => $vvhc){
						foreach($vvhc as $vhc){
							$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." and kodebudget='".$kdbgt."' and kodevhc='".$vhc."' order by kodeorg asc";
							$res=fetchdata($str);
							$row=0;
							foreach($res as $bar){
								$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['aruskas']."'");
								$row++;
								$no++;
								
								$nopol="";
								if($optnopol[$bar['kodevhc']]!=''){
									$nopol=" - ".$optnopol[$bar['kodevhc']];
								}
								$tab.="<tr class='rowcontent' id=vhc_".$no." ".$style.">";
								$tab.="<td style='text-align:right;'>".$no."</td>";
								$tab.="<td style='text-align:left;'>".getNamaOrg($stat)."</td>";
								$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
								$tab.="<td style='text-align:left;'>".$nmkode[$bar['kodebudget']]."</td>";
								$tab.="<td style='text-align:center;'>".$bar['noakun']."</td>";
								$tab.="<td style='text-align:left;'>".$nmakun[$bar['noakun']]."</td>";
								$tab.="<td style='text-align:left;'>".$bar['aruskas']." - ".$nmkas[$bar['aruskas']]."</td>";
								$tab.="<td style='text-align:left;'>".$bar['kodevhc']."".$nopol." - ".getNopol($bar['kodevhc'],'x')."</td>";
								$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['jumlah'],2)."</td>";
								$tab.="<td style='text-align:center;'>".$bar['satuanj']."</td>";
								$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rupiah'])."</td>";
								if($param['jenis']!='excel'){					
									if($param['tipe']!='popup'){
										$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editvhc('".$bar['kunci']."','".$bar['kodeorg']."','".$bar['aruskas']."','".$bar['kodebudget']."','".$bar['jumlah']."','".$bar['rupiah']."','".$bar['kodevhc']."','".$bar['satuanj']."');\" ></td>";
										
										$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('".$bar['kunci']."','vhc');\" title='Delete'></td>";
									}else{
										$tab.="<td align=center width=25px></td>";
										$tab.="<td align=center width=25px></td>";
									}		
								}
								$tab.="</tr>";							
								$awal=($no-$row)+1;
							}
							
							$nopol="";
							if($optnopol[$vhc]!=''){
								$nopol=" - ".$optnopol[$vhc];
							}
							$nott++;
							if($param['blok']!=''){
								$isi="<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('".$awal."','".$no."','vhc');\">";
							}else{				
								$isi="<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('".$awal."','".$no."','vhc');\">";
							}
							$click="onclick=\"showhide('".$awal."','".$no."','vhc');\"";
							$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$dtb[$stat][$kdbgt][$vhc]['kas']."'");
							$tab.="<tr class='rowcontent' ".$stytt.">";
							$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$nott."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmorg[$stat]."</td>";
							$tab.="<td style='text-align:center;background-color:#CAFFF4;' id=plusvhc".$awal.">".$isi."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmkode[$kdbgt]."</td>";
							$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$vhc]['acc']."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmakun[$dtb[$stat][$kdbgt][$vhc]['acc']]."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$vhc]['kas']." - ".$nmkas[$dtb[$stat][$kdbgt][$vhc]['kas']]."</td>";
							$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$vhc."".$nopol." - ".getNopol($bar['kodevhc'],'x')."</td>";
							$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt][$vhc]['jlh'],2)."</td>";
							$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$vhc]['satj']."</td>";
							$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt][$vhc]['rp'])."</td>";
							if($param['jenis']!='excel'){					
								if($param['tipe']!='popup'){										
									$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editvhc('','','".$dtb[$stat][$kdbgt][$vhc]['kas']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$vhc]['jlh']."','".$dtb[$stat][$kdbgt][$vhc]['rp']."','".$vhc."','".$dtb[$stat][$kdbgt][$vhc]['satj']."');\" ></td>";
									
									$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('vhc','".$param['tahun']."','".$param['station']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$vhc]['acc']."','','".$vhc."');\" title='Delete'></td>";
								}else{
									$tab.="<td align=center width=25px></td>";
									$tab.="<td align=center width=25px></td>";
								}		
							}
							
							$ttljlh+=$dtb[$stat][$kdbgt][$vhc]['jlh'];
							$ttlrp+=$dtb[$stat][$kdbgt][$vhc]['rp'];
							$tab.="</tr>";
						}
					}
				}
				
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;' colspan=8>TOTAL</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttljlh,2)."</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttlrp,0)."</td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
			
		$tab.="</tbody></table></div>";	
		
		echo $tab;
	break;
	case'loaddatakont':
		if($param['tipe']!='popup'){			
			// $tab.="<div class='table-scroll'>";
		}
		$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['station']."</th>
					<th align=center>".$_SESSION['lang']['mesin']."</th>
					<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
					<th align=center style='width:55px'>".$_SESSION['lang']['noakun']."</th>
					<th align=center>".$_SESSION['lang']['namaakun']."</th>
					<th align=center>".$_SESSION['lang']['aruskas']."</th>
					<th align=center>".$_SESSION['lang']['namabarang']."</th>
					<th align=center>".$_SESSION['lang']['keterangan']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['rp']."</th>
					<th align=center colspan=2>Action</th>
				</tr>
			</thead><tbody>";
			
			if($param['mesin']!=''){
				$style="";
				$stytt="style=cursor:pointer;height:25px;display:none";
				$wh=" and a.kodeorg like '".$param['mesin']."%'";
			}else{				
				$stytt="style=cursor:pointer;height:25px";
				if($param['tipe']!='popup'){					
					$style="style=display:none";
				}
				$wh=" and a.kodeorg like '".$param['station']."%'";
				$wh.=" and substr(a.kodeorg,1,4) = '".$param['kodeorg']."'";
			}
			$wh.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
			$wh.=" and a.tahunbudget = '".$param['tahun']."'";
			$wh.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
			$wh.=" and kodebudget='KONTRAK'";
			
			$optnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol');
			
			$data=array();
			$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." order by kodeorg asc, kodebudget asc, kodevhc asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]=$bar['keterangan'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['acc']=$bar['noakun'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['kas']=$bar['aruskas'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['satv']=$bar['satuanv'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['satj']=$bar['satuanj'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['rot']=$bar['rotasi'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['brg']=$bar['kodebarang'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['ket']=$bar['keterangan'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['vol']+=$bar['volume'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['jlh']+=$bar['jumlah'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['rp']+=$bar['rupiah'];
			}
			if(count($res)>0){
				$no=0;
				foreach($data as $stat => $vkdbgt){
					foreach($vkdbgt as $kdbgt => $vakun){
						foreach($vakun as $akun => $vket){
							foreach($vket as $ket){
								if($ket!=''){
									$whket=" and keterangan='".$ket."'";
								}else{
									$whket=" and (keterangan is null or keterangan='')";
								}
								$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." and kodebudget='".$kdbgt."' and noakun='".$akun."' ".$whket." order by kodeorg asc";
								// echo $str.";<br>";
								$res=fetchdata($str);
								$row=0;
								foreach($res as $bar){
									$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['aruskas']."'");
									$row++;
									$no++;
									
									$tab.="<tr class='rowcontent' id=kont_".$no." ".$style.">";
									$tab.="<td style='text-align:right;'>".$no."</td>";
									$tab.="<td style='text-align:left;'>".getNamaOrg($stat)."</td>";
									$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
									$tab.="<td style='text-align:left;'>".$nmkode[$bar['kodebudget']]."</td>";
									$tab.="<td style='text-align:center;'>".$bar['noakun']."</td>";
									$tab.="<td style='text-align:left;'>".$nmakun[$bar['noakun']]."</td>";
									$tab.="<td style='text-align:left;'>".$bar['aruskas']." - ".$nmkas[$bar['aruskas']]."</td>";
									$tab.="<td style='text-align:left;'>".$bar['kodebarang']." - ".getNamaBrg($bar['kodebarang'])."</td>";
									$tab.="<td style='text-align:left;'>".$bar['keterangan']."</td>";
									$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['jumlah'],2)."</td>";
									$tab.="<td style='text-align:center;'>".$bar['satuanj']."</td>";
									$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rupiah'])."</td>";
									if($param['jenis']!='excel'){					
										if($param['tipe']!='popup'){
											$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editkontrak('".$bar['kunci']."','".$bar['kodeorg']."','".$bar['aruskas']."','".$bar['kodebudget']."','".$bar['jumlah']."','".$bar['rupiah']."','".$bar['satuanj']."','".$bar['noakun']."','".$bar['keterangan']."','".$bar['kodebarang']."','".getNamaBrg($bar['kodebarang'])."');\" ></td>";
											
											$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('".$bar['kunci']."','kont');\" title='Delete'></td>";
										}else{
											$tab.="<td align=center width=25px></td>";
											$tab.="<td align=center width=25px></td>";
										}		
									}
									$tab.="</tr>";							
									$awal=($no-$row)+1;
								}
								
								$nott++;
								if($param['blok']!=''){
									$isi="<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('".$awal."','".$no."','kont');\">";
								}else{				
									$isi="<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('".$awal."','".$no."','kont');\">";
								}
								$click="onclick=\"showhide('".$awal."','".$no."','kont');\"";
								$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$dtb[$stat][$kdbgt][$akun][$ket]['kas']."'");
								$tab.="<tr class='rowcontent' ".$stytt.">";
								$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$nott."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmorg[$stat]."</td>";
								$tab.="<td style='text-align:center;background-color:#CAFFF4;' id=pluskont".$awal.">".$isi."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmkode[$kdbgt]."</td>";
								$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$akun][$ket]['acc']."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmakun[$dtb[$stat][$kdbgt][$akun][$ket]['acc']]."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$akun][$ket]['kas']." - ".$nmkas[$dtb[$stat][$kdbgt][$akun][$ket]['kas']]."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$akun][$ket]['brg']." - ".getNamaBrg($dtb[$stat][$kdbgt][$akun][$ket]['brg'])."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$ket."</td>";
								$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt][$akun][$ket]['jlh'],2)."</td>";
								$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$akun][$ket]['satj']."</td>";
								$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt][$akun][$ket]['rp'])."</td>";
								if($param['jenis']!='excel'){					
									if($param['tipe']!='popup'){										
										$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editkontrak('','','".$dtb[$stat][$kdbgt][$akun][$ket]['kas']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$akun][$ket]['jlh']."','".$dtb[$stat][$kdbgt][$akun][$ket]['rp']."','".$dtb[$stat][$kdbgt][$akun][$ket]['satj']."','".$akun."','".$ket."','".$dtb[$stat][$kdbgt][$akun][$ket]['brg']."','".getNamaBrg($dtb[$stat][$kdbgt][$akun][$ket]['brg'])."');\" ></td>";
										
										$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('kont','".$param['tahun']."','".$param['station']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$akun][$ket]['acc']."','','','".$ket."');\" title='Delete'></td>";
									}else{
										$tab.="<td align=center width=25px></td>";
										$tab.="<td align=center width=25px></td>";
									}		
								}
								
								$ttljlh+=$dtb[$stat][$kdbgt][$akun][$ket]['jlh'];
								$ttlrp+=$dtb[$stat][$kdbgt][$akun][$ket]['rp'];
								$tab.="</tr>";
							}
						}	
					}
				}
				
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;' colspan=9>TOTAL</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttljlh,2)."</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttlrp,0)."</td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
			
		$tab.="</tbody></table></div>";	
		
		echo $tab;
	break;
	case'loaddatalain':
		if($param['tipe']!='popup'){			
			// $tab.="<div class='table-scroll'>";
		}
		$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<th align=center width=30px>No.</th>
					<th align=center>".$_SESSION['lang']['station']."</th>
					<th align=center>".$_SESSION['lang']['mesin']."</th>
					<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
					<th align=center style='width:55px'>".$_SESSION['lang']['noakun']."</th>
					<th align=center>".$_SESSION['lang']['namaakun']."</th>
					<th align=center>".$_SESSION['lang']['aruskas']."</th>
					<th align=center>".$_SESSION['lang']['keterangan']."</th>
					<th align=center>".$_SESSION['lang']['rp']."</th>
					<th align=center colspan=2>Action</th>
				</tr>
			</thead><tbody>";
			
			if($param['mesin']!=''){
				$style="";
				$stytt="style=cursor:pointer;height:25px;display:none";
				if(strlen($param['station'])<=4){
					$wh=" and a.kodeorg = '".$param['mesin']."'";
				}else{					
					$wh=" and a.kodeorg like '".$param['mesin']."%'";
				}
			}else{				
				$stytt="style=cursor:pointer;height:25px";
				if($param['tipe']!='popup'){					
					$style="style=display:none";
				}
				if(strlen($param['station'])<=4){
					$wh=" and a.kodeorg = '".$param['station']."'";
				}else{
					$wh=" and a.kodeorg like '".$param['station']."%'";					
				}
				$wh.=" and substr(a.kodeorg,1,4) = '".$param['kodeorg']."'";
			}
			$wh.=" and substr(a.kodeorg,1,4) in (".getOrgDetail(2).")";
			$wh.=" and a.tahunbudget = '".$param['tahun']."'";
			$wh.=" and a.tipebudget = 'MILL' and a.kodebudget!='UMUM' and a.pta='BGT'";
			$wh.=" and kodebudget in ('LAIN','CPO','TBS','KER')";
			
			$optnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol');
			
			$data=array();
			$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." order by kodeorg asc, kodebudget asc, kodevhc asc";
			$res=fetchdata($str);
			foreach($res as $bar){
				$data[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]=$bar['keterangan'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['acc']=$bar['noakun'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['kas']=$bar['aruskas'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['satv']=$bar['satuanv'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['satj']=$bar['satuanj'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['rot']=$bar['rotasi'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['ket']=$bar['keterangan'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['vol']+=$bar['volume'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['jlh']+=$bar['jumlah'];
				$dtb[$bar['station']][$bar['kodebudget']][$bar['noakun']][$bar['keterangan']]['rp']+=$bar['rupiah'];
			}
			if(count($res)>0){
				$no=0;
				foreach($data as $stat => $vkdbgt){
					foreach($vkdbgt as $kdbgt => $vakun){
						foreach($vakun as $akun => $vket){
							foreach($vket as $ket){
								if($ket!=''){
									$whket=" and keterangan='".$ket."'";
								}else{
									$whket=" and (keterangan is null or keterangan='')";
								}
								$str="select a.*,substr(kodeorg,1,6) as station from ".$dbname.".bgt_budget a where 1=1 ".$wh." and kodebudget='".$kdbgt."' and noakun='".$akun."' ".$whket." order by kodeorg asc";
								// echo $str.";<br>";
								$res=fetchdata($str);
								$row=0;
								foreach($res as $bar){
									$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['aruskas']."'");
									$row++;
									$no++;
									
									$tab.="<tr class='rowcontent' id=lain_".$no." ".$style.">";
									$tab.="<td style='text-align:right;'>".$no."</td>";
									$tab.="<td style='text-align:left;'>".getNamaOrg($stat)."</td>";
									$tab.="<td style='text-align:left;'>".getNamaOrg($bar['kodeorg'])."</td>";
									$tab.="<td style='text-align:left;'>".$nmkode[$bar['kodebudget']]."</td>";
									$tab.="<td style='text-align:center;'>".$bar['noakun']."</td>";
									$tab.="<td style='text-align:left;'>".$nmakun[$bar['noakun']]."</td>";
									$tab.="<td style='text-align:left;'>".$bar['aruskas']." - ".$nmkas[$bar['aruskas']]."</td>";
									$tab.="<td style='text-align:left;'>".$bar['keterangan']."</td>";
									
									$tab.="<td style='text-align:right;'>".hidezerodecimal($bar['rupiah'])."</td>";
									if($param['jenis']!='excel'){					
										if($param['tipe']!='popup'){
											$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editlain('".$bar['kunci']."','".$bar['kodeorg']."','".$bar['aruskas']."','".$bar['kodebudget']."','".$bar['jumlah']."','".$bar['rupiah']."','".$bar['satuanj']."','".$bar['noakun']."','".$bar['keterangan']."');\" ></td>";
											
											$tab.="<td align=center width=25px><img class=zImgBtn src=images/application/application_delete.png onclick=\"delbyindex('".$bar['kunci']."','lain');\" title='Delete'></td>";
										}else{
											$tab.="<td align=center width=25px></td>";
											$tab.="<td align=center width=25px></td>";
										}		
									}
									$tab.="</tr>";							
									$awal=($no-$row)+1;
								}
								
								$nott++;
								if($param['blok']!=''){
									$isi="<img src=images/menu/symbol_2.gif class=zImgBtn title='Expand' onclick=\"showhide('".$awal."','".$no."','lain');\">";
								}else{				
									$isi="<img src=images/menu/symbol_1.gif class=zImgBtn title='Collaps' onclick=\"showhide('".$awal."','".$no."','lain');\">";
								}
								$click="onclick=\"showhide('".$awal."','".$no."','lain');\"";
								$nmkas = makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$dtb[$stat][$kdbgt][$akun][$ket]['kas']."'");
								$tab.="<tr class='rowcontent' ".$stytt.">";
								$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$nott."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmorg[$stat]."</td>";
								$tab.="<td style='text-align:center;background-color:#CAFFF4;' id=pluslain".$awal.">".$isi."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmkode[$kdbgt]."</td>";
								$tab.="<td ".$click." style='text-align:center;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$akun][$ket]['acc']."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$nmakun[$dtb[$stat][$kdbgt][$akun][$ket]['acc']]."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$dtb[$stat][$kdbgt][$akun][$ket]['kas']." - ".$nmkas[$dtb[$stat][$kdbgt][$akun][$ket]['kas']]."</td>";
								$tab.="<td ".$click." style='text-align:left;background-color:#CAFFF4;'>".$ket."</td>";
								$tab.="<td ".$click." style='text-align:right;background-color:#CAFFF4;'>".hidezerodecimal($dtb[$stat][$kdbgt][$akun][$ket]['rp'])."</td>";
								if($param['jenis']!='excel'){					
									if($param['tipe']!='popup'){										
										$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editlain('','','".$dtb[$stat][$kdbgt][$akun][$ket]['kas']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$akun][$ket]['jlh']."','".$dtb[$stat][$kdbgt][$akun][$ket]['rp']."','".$dtb[$stat][$kdbgt][$akun][$ket]['satj']."','".$akun."','".$ket."');\" ></td>";
										
										$tab.="<td align=center width=25px style='background-color:#CAFFF4;'><img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('lain','".$param['tahun']."','".$param['station']."','".$kdbgt."','".$dtb[$stat][$kdbgt][$akun][$ket]['acc']."','','','".$ket."');\" title='Delete'></td>";
									}else{
										$tab.="<td align=center width=25px></td>";
										$tab.="<td align=center width=25px></td>";
									}		
								}
								
								$ttljlh+=$dtb[$stat][$kdbgt][$akun][$ket]['jlh'];
								$ttlrp+=$dtb[$stat][$kdbgt][$akun][$ket]['rp'];
								$tab.="</tr>";
							}
						}	
					}
				}
				
				$tab.="<tr class='rowcontent' style='height:25px'>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;' colspan=6>TOTAL</td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:right;background-color:#E5E8E8;'>".hidezerodecimal($ttlrp,0)."</td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
				$tab.="<td style='text-align:center;background-color:#E5E8E8;'></td>";
			}
			
		$tab.="</tbody></table></div>";	
		
		echo $tab;
	break;
	case'delbyindex':
		try{
		$owlPDO->beginTransaction();
			
			$str="delete from ".$dbname.".bgt_budget  where kunci='".$param['index']."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'deldetail':
		try{
		$owlPDO->beginTransaction();
			
			$whr=$wh="";
			$whr.=" and `tahunbudget` = '".$param['tahun']."'";
			$whr.=" and `kodebudget` = '".$param['kdbudget']."'";
			$whr.=" and `kodeorg` like '".$param['station']."%'";
			if($param['kodebarang']!=''){
				$whr.=" and `kodebarang` = '".$param['kodebarang']."'";			
			}
			if($param['kodevhc']!=''){
				$whr.=" and `kodevhc` = '".$param['kodevhc']."'";
			}
			if($param['noakun']!=''){
				$whr.=" and `noakun` = '".$param['noakun']."'";
			}
			if($param['keterangan']!='' and $param['keterangan']!='undefined'){
				$whr.=" and `keterangan` = '".$param['keterangan']."'";
			}else{
				$whr.=" and (`keterangan` is null or `keterangan`='')";
			}
			
			$str="select * from ".$dbname.".bgt_budget where 1=1 and `tipebudget` = '".$tipebudget."' ".$whr.""; //exit("error".$str);
			$res=fetchdata($str);
			foreach($res as $bar){
				$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
				$owlPDO->exec($str);
				
				$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'fileSelected':
		if($param['tahun']==''){
			exit("Warning : Tahun budget wajib diisi.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode traksi wajib diisi.");
		}
		
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' and closed=1 ";
		$res=fetchData($str);
		foreach($res as $val){
			$harga[$val['kodebarang']]=$val['hargasatuan'];
		}
			
		$data = $_POST;
		
		$jenismatarr=array('consumables'=>'consumables','recurrent'=>'recurrent','nonrecurrent'=>'nonrecurrent');
		
		
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file = $_FILES['file']['tmp_name'];		
			
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);
				$arritem=array(); $dtslh=0;
				foreach ($sheets as $noitem => $sheet){
					if($noitem>1 and $sheet['A']!=''){
						if($param['jenis']=='simpan'){
							try {
							$owlPDO->beginTransaction();
								$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$sheet['A']."'");
								
								$kodebudget       = "M-".substr($sheet['A'],0,3);
								$param['kdbudget']= "M-".substr($sheet['A'],0,3);
								$param['rupiah']  = $sheet['B']*$harga[$sheet['A']];
								$param['jumlah']  = $sheet['B'];
								
								$wh="";
								if($param['mesin']!=''){
									$whr="and `kodeorg` like '".$param['mesin']."%'";
									$wh.="and `kodeorganisasi` like '".$param['mesin']."%'";
								}else{
									$whr="and `kodeorg` like '".$param['station']."%'";
									$wh.="and `induk` = '".$param['station']."'";
								}
								
								$str="select * from ".$dbname.".bgt_budget where `tahunbudget` = '".$param['tahun']."' and `tipebudget` = '".$tipebudget."' ".$whr." and `kodebudget` = '".$param['kdbudget']."' and `kodebarang` = '".$sheet['A']."'";
								$res=fetchdata($str);
								foreach($res as $bar){
									$str="delete from ".$dbname.".bgt_distribusi where kunci='".$bar['kunci']."'";
									$owlPDO->exec($str);
									
									$str="delete from ".$dbname.".bgt_budget where kunci='".$bar['kunci']."'";
									$owlPDO->exec($str);
								}
								
								$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where 1=1 ".$wh."";
								$res = fetchdata($str);
								$jlh = count($res);
								if($jlh>0){
									$no=0;$tjlh=$trp=0;
									foreach($res as $bar){
										$no++;
										if($no<$jlh){
											$jumlah = round(($param['jumlah']/$jlh),5);
											$totalrp= round(($param['rupiah']/$jlh),0);
											
											$tjlh+=$jumlah;
											$trp+=$totalrp;
										}else{
											$jumlah = $param['jumlah']-$tjlh;
											$totalrp= $param['rupiah']-$trp;
										}
										
										$param['noakun']  = $sheet['C'];
										$param['aruskas'] = $sheet['D'];
										$param['jenismat']= $sheet['E'];
										
										$data = array(
											'tahunbudget'=> $param['tahun'],
											'kodeorg'    => $bar['kodeorganisasi'],
											'tipebudget' => $tipebudget,
											'kodebudget' => $param['kdbudget'],
											'noakun'     => $param['noakun'],
											'rupiah'     => $totalrp,
											'updateby'   => $_SESSION['standard']['userid'],
											'jumlah'     => $jumlah,
											'satuanj'    => $nmsat[$sheet['A']],
											'aruskas'    => $param['aruskas'],
											'kodebarang' => $sheet['A'],
											'keterangan' => $param['jenismat'],
											'regional'   => $region
										);
										
										$cols = array();
										foreach($data as $key=>$row) {
											$cols[] = $key;
										}

										$query = insertQuery($dbname,'bgt_budget',$data,$cols);
										if($totalrp>0){
											$owlPDO->exec($query);
										}
									}
								}
								
							$owlPDO->commit();
							} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
						}else{
							$akunsalah="";
							if(substr($sheet['C'],0,2)!='63' or getNamaAkun($sheet['C'])==''){
								$akunsalah="style=background-color:red;";
								$dtslh=1;
							}
							
							$str="select * from ".$dbname.".keu_5aruskas_detail where `noakun` = '".$sheet['C']."'";
							$res=fetchdata($str);
							foreach($res as $bar){
								$datakas[$bar['noaruskas']]=$bar['noaruskas'];
							}							
							$arussalah="";
							if($datakas[$sheet['D']]==""){
								#$arussalah="style=background-color:red;";									
								#$dtslh=1;
							}
							
							
							$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$sheet['A']."'");
							$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$sheet['A']."'");
							$brgsalah="";
							if($nmbrg[$sheet['A']]==''){
								$dtslh=1;
								$brgsalah="style=background-color:red;";									
							}
							$jnssalah="";
							if($jenismatarr[$sheet['E']]==''){
								$dtslh=1;
								$jnssalah="style=background-color:red;";									
							}
							
							
							$no++;
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=center>".$no."</td>";
							$tab.="<td align=center>".$param['tahun']."</td>";
							$tab.="<td align=center>".$region."</td>";
							$tab.="<td align=center ".$akunsalah.">".$sheet['C']."</td>";
							$tab.="<td align=center ".$arussalah.">".$sheet['D']."</td>";
							$tab.="<td align=center ".$jnssalah.">".$sheet['E']."</td>";
							$tab.="<td align=center>".$sheet['A']."</td>";
							$tab.="<td align=left ".$brgsalah.">".$nmbrg[$sheet['A']]."</td>";
							$tab.="<td align=center>".$nmsat[$sheet['A']]."</td>";
							$tab.="<td align=right>".$sheet['B']."</td>";
							$tab.="<td align=right>".number_format($harga[$sheet['A']])."</td>";
							$tab.="<td align=right>".number_format($sheet['B']*$harga[$sheet['A']])."</td>";
							$tab.="</tr>";
							
							$ttl+=$sheet['B']*$harga[$sheet['A']];
						}
					}
				}
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=11 align=center>TOTAL</td>";
				$tab.="<td align=right>".number_format($ttl)."</td>";
				$tab.="</tr>";
				if($dtslh==0){		
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=12 align=center><button id=btnsubmit class=mybutton onclick=\"fileSelected('simpan')\">SaveAll</button></td>";
					$tab.="</tr>";
				}else{
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=12 align=center>Masih terdapat data yang salah, silahkan perbaiki terlebih dahulu.</td>";
					$tab.="</tr>";
				}
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
		
		echo $tab;
	break;
	case'showupload':
		$tab="";
		//$tab.="<fieldset><legend>Upload / Download</legend>";
		$tab.="<table border=0>
			<tr>
				<td>Download</td>
				<td>:</td>
				<td><button class=mybutton onclick=\"downloadmaster()\">Master Barang</button></td>
				<td colspan=4><button class=mybutton ><a href='tool_slave_getExample.php?form=BGTPKS' target='frame'>Template</a></button></td>
			</tr>
			<tr>
				<td>Upload</td>
				<td>:</td>
				<td colspan=6>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td colspan=7>
					<button id=btnsubmit class=mybutton onclick=\"fileSelected()\">Preview</button>
				</td>
			</tr>
		</table>";
		$tab.="</fieldset>";
		//$tab.="<fieldset><legend>".$_SESSION['lang']['list']."</legend>";
		$tab.="
			<table class='sortable' cellpadding=5 cellspacing='1' border='0'>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center'>Tahun</th>
					<th align='center'>Regional</th>
					<th align='center'>Akun</th>
					<th align='center'>Arus Kas</th>
					<th align='center'>Jenis</th>
					<th align='center'>Kode Barang</th>
					<th align='center'>Nama Barang</th>
					<th align='center'>Satuan</th>
					<th align='center'>Jumlah</th>
					<th align='center'>Harga</th>
					<th align='center'>Rupiah</th>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table></div>";

		// $tab.="</fieldset>";
		echo $tab;
	break;
	case'downloadmaster':
		if($param['tahun']==''){
			exit("Warning : Tahun budget wajib diisi.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode unit wajib diisi.");
		}
	
		$tab="";
		$tab.="
			<table class='sortable' cellspacing='1' border='1'>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center'>Kode Barang</th>
					<th align='center'>Nama Barang</th>
					<th align='center'>Satuan</th>
					<th align='center'>Harga</th>
				</tr>
				</thead>
				<tbody>";
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' and closed=1 ";
		$val=fetchData($str);
		foreach($val as $res){
			$sDt="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
			$nm=fetchData($sDt)[0];
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$res['kodebarang']."</td>";
			$tab.="<td>".$nm['namabarang']."</td>";
			$tab.="<td>".$nm['satuan']."</td>";
			$tab.="<td align=right>".@number_format($res['hargasatuan'])."</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody>
		</table>";
		
		$nop = "masterbarang.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("masterbarang", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();

	break;
	
}
function cekheader($param){
	global $param;
	global $dbname;
	
	if($param['tahun']==''){
		exit("Warning : Tahun budget wajib diisi.");
	}
	if(strlen($param['tahun'])<4){
		exit("Warning : Tahun budget salah.");
	}
	if($param['kodeorg']==''){
		exit("Warning : Kode organisasi wajib diisi.");
	}
	if($param['station']==''){
		exit("Warning : Station wajib diisi.");
	}
	if($param['kodeorg']!=$_SESSION['empl']['lokasitugas']){
		exit("Warning : Silahkan pindah ke ".$param['kodeorg']." terlebih dahulu.");
	}
	
	
	$whr = " and kodeorg like '".$param['kodeorg']."%' and tipebudget='MILL' and tahunbudget='".$param['tahun']."' and pta='BGT' and kodebudget != 'UMUM'";
	$str="select * from ".$dbname.".bgt_budget where 1=1 ".$whr." and tutup='1'  and kodeorg like '".$param['station']."%'";
	$res=fetchdata($str);
	if(count($res)>0){
		exit("Warning : Budget sudah ditutup.");
	}
}
function createpagingsebar($jlhbrs,$limit,$page,$colspan,$loaddata,$getpage){
	global $dbname;
	global $owlPDO;
	
	$tab="";
	$totrows=ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	
	$isiRow='';
	for($er=1;$er<=$totrows;$er++){
		$sel = ($page==$er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	
	$frompage = (($page*$limit)+1);
	if((($page+1)*$limit) > $jlhbrs){
		$topage = $jlhbrs;
	}else{
		$topage = (($page+1)*$limit);
	}
	$tab.="<tfoot><tr>
		<td colspan=".$colspan." align=center>
			".$frompage." to ".$topage." Of ".  $jlhbrs."
		</td>
	</tr>
	<tr>
		<td colspan=".$colspan." align=center>";
			if($page=='0'){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=$loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>";
			}
			$tab.="<select id=\"pagessbr\" name=\"pagessbr\" style=\"min-width:20px\" onchange=\"$getpage()\">".$isiRow."</select>";
			
			if(($page+1) == $totrows){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=$loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
			}
		$tab.="</td>
	</tr>
	</tfoot>";
	
	return $tab;
}
?>	