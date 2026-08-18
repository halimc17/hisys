<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$jenisApp='BTBS';
$arrnmsupp = array();
$arrnmorg = array();
$str="select a.namasupplier,b.kodetimbangan from ".$dbname.".log_5supplier a
left join ".$dbname.".log_5suptimbangan b on a.supplierid=b.supplierid
left join ".$dbname.".log_5supkelompok c on a. supplierid=c.supplierid
where c.tipe in ('SUPPLIERTBS','SUPPLIERTBSKUD')";
$res=fetchdata($str);
foreach($res as $val)
{
	$arrnmsupp[$val['kodetimbangan']] = $val['namasupplier'];
}


$str="select * from ".$dbname.".organisasi
where tipe in ('KEBUN')";
$res=fetchdata($str);
foreach($res as $val)
{
	$arrnmorg[$val['kodeorganisasi']] = $val['namaorganisasi'];
}


// $arrnmsupp=  makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier',"supplierid in (select supplierid from ".$dbname.".log_5supkelompok where tipe='SUPPLIERTBS')");

if(isset($_POST['method'])){
	$param=$_POST;	
}else{
	$param=$_GET;
}

switch ($method) {
	case'getPabrik':
	/*
		$sReg="select distinct(a.supplierid),a.namasupplier,b.kodetimbangan as kodecustomer from ".$dbname.".log_5supplier  a
		left join ".$dbname.".log_5suptimbangan b on a.supplierid=b.supplierid
		left join ".$dbname.".log_5supkelompok c on a.supplierid=c.supplierid
		where c.tipe='SUPPLIERTBS' and a.status='1' and b.status='1' order by a.namasupplier";
		// exit ("error : $sReg");
		$resData=fetchdata($sReg);
		$optunit.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($resData as $isiData){
			if($param['kdcust']!=''){
				$optunit.="<option value='".$isiData['kodecustomer']."' ".($param['kdcust']==$isiData['kodecustomer']?'selected':'').">".$isiData['namasupplier']."</option>";	
			}else{
				$optunit.="<option value='".$isiData['kodecustomer']."'>".$isiData['namasupplier']."</option>";
			}
		}
		*/
		
		
		
		
		$sReg="select distinct (kodecustomer) as kodecustomer,kodeorg from ".$dbname.".pabrik_timbangan
			   where millcode='".$param['kdPabrik']."' and left(tanggal,10)='".tanggalsystemn($param['tgl'])."'
			   and intex in (0,2) and kodebarang='40000003'";
		$resData=fetchdata($sReg);
		$optunit.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($resData as $isiData){
			if($param['kdcust']!=''){
				$optunit.="<option value='".$isiData['kodecustomer']."' ".($param['kdcust']==$isiData['kodecustomer']?'selected':'').">".$arrnmsupp[$isiData['kodecustomer']]."</option>";	
			}else{
				$optunit.="<option value='".$isiData['kodecustomer']."'>".$arrnmsupp[$isiData['kodecustomer']]."</option>";
			}
		}
		
		
		
		
		echo $optunit;
	break;
	case'getpricetbs':
		$hargatbs = 0;
		$optSup = makeOption($dbname,'log_5suptimbangan','kodetimbangan,supplierid',"kodetimbangan='".$param['suppId']."'");
		
		$str="select * from ".$dbname.".pmn_hargabelitbs where 
			tanggal <= '".$tgl."' and kodeorg='".$param['unit']."' and supplierid='".$optSup[$param['suppId']]."'
			order by tanggal desc limit 1";
		$res=fetchData($str);
		if(count($res) > 0)
		{
			$hargatbs = $res[0]['hargabudget'];
		}
		
		$subsidi=0;
		// $netto=0;
		// $str="select sum(beratbersih-kgpotsortasi) as netto from ".$dbname.".pabrik_timbangan 
				// where kodebarang='40000003' and left(tanggal,10)='".tanggalsystemn($param['tgl'])."' and millcode='".$param['unit']."' and kodecustomer='".$param['suppId']."'";
		// $res=fetchData($str);
		// $netto=$res[0]['netto'];
		// if($netto >= 50000 && $netto < 100000)
		// {
			// $subsidi = 5;
		// }
		// else if($netto >= 100000)
		// {
			// $subsidi = 10;
		// }
		
		echo $hargatbs."####".$subsidi;
	break;
	case'detail':
		$str=" select notransaksi,left(tanggal,10) as tanggal,beratmasuk as bruto,
				beratkeluar as tara,beratbersih as netto,kgpotsortasi as potongan,
				kodecustomer,nokendaraan from ".$dbname.".pabrik_timbangan 
				where kodebarang='40000003' and intex=0 and left(tanggal,10)='".tanggalsystemn($param['tglnormal'])."' and millcode='".$param['millcode']."'
				and kodecustomer='".$param['suppId']."'";
				// echo $str;
		$dt=fetchdata($str);
		if(count($dt)==0){
			exit("warning: Tidak ada buah masuk pada ".$param['millcode']." di tanggal :".$param['tglnormal']);
		}
		$form="";
		$form.="<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
		$form.="<table cellpading=1 cellspacing=1 border=0 class=sortable>
                <thead>
				<tr class=rowheader>
					<td rowspan=2 align=center>No.</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['noTiket']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['supplier']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['kodevhc']." </td>
					<td rowspan=2 align=center>Bruto</td>
					<td rowspan=2 align=center>Tara</td>
					<td rowspan=2 align=center>Netto 1</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['potongankg']." </td>
					<td rowspan=2 align=center>Netto 2</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['bonus']."/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>Subsidi</td>
					<td rowspan=2 align=center style=width:80px>".$_SESSION['lang']['notiftotalbayar']." ke ".$_SESSION['lang']['supplier']."</td>
					<td colspan=3 align=center>Beban Pajak PPh 22</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." ".$_SESSION['lang']['all']."</td>
				</tr><tr>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['persen']."</td>
				<td align=center>".$_SESSION['lang']['rp']."</td>
				</tr></thead>";
		if($param['notransaksi']!=''){
			$sData="select * from ".$dbname.".keu_persediaantbs_dt where notransaksi='".$param['notransaksi']."'";
			$qData=fetchdata($sData);
			foreach($qData as $lstData){
				$whrsup="supplierid='".$lstData['kodesupplier']."'";
				$optSupp=makeOption($dbname,'log_5suptimbangan','supplierid,kodetimbangan',$whrsup);
            	$suppId=$optSupp[$lstData['kodesupplier']];
				$hargadt[$suppId.$lstData['klasifikasi']]=$lstData['harga_perkg'];
				$bonusdt[$suppId.$lstData['klasifikasi']]=$lstData['bonus_perkg'];
				$subsididt[$suppId.$lstData['klasifikasi']]=$lstData['subsidi'];
				$persenpajak[$suppId.$lstData['klasifikasi']]=$lstData['persenpajak'];
				$bbnPajak[$suppId.$lstData['klasifikasi']]=$lstData['beban_pajak'];
				$totRupiah[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiah'];
			}
		}
		$str=" select notransaksi,left(tanggal,10) as tanggal,beratmasuk as bruto,
				beratkeluar as tara,beratbersih as netto,kgpotsortasi as potongan,
				kodecustomer,nokendaraan from ".$dbname.".pabrik_timbangan 
				where kodebarang='40000003' and intex=0 and left(tanggal,10)='".tanggalsystemn($param['tglnormal'])."' 
				and millcode='".$param['millcode']."' and kodecustomer='".$param['suppId']."'";
				// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$showsubsidi = 0;
		while($bar=$res->fetch())
		{
			$showsubsidi = $showsubsidi + $bar['netto'];
		}
		
		// $param['subsidiangkut'] = 0;
		// if($showsubsidi >= 30000 && $showsubsidi < 100000)
		// {
			// $param['subsidiangkut'] = 5;
		// }
		// else if($showsubsidi >= 100000)
		// {
			// $param['subsidiangkut'] = 10;
		// }
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			$beratnormal=$bar['netto']-$bar['potongan'];
			$prsn=$param['prsnAll'];

			if($param['notransaksi']!=''){
				$bar['id_klasifikasi']=$bar['notransaksi'];
				$totalharga=($beratnormal*$hargadt[$bar['kodecustomer'].$bar['id_klasifikasi']])+($beratnormal*$subsididt[$bar['kodecustomer'].$bar['id_klasifikasi']])+($beratnormal*$bonusdt[$bar['kodecustomer'].$bar['id_klasifikasi']]);
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==1){
					$rppph= ($totalharga * $persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']] / 100);
				}else{
					$rppph=0;
				}
				$totdgnpph=$totalharga-$rppph;
				$hargaperdata=$hargadt[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$bonusperdata=$bonusdt[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$hargasubsidi=$subsididt[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$dt="";
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==0){
					$dt="selected";
				}
			}else{
				$totalharga=($beratnormal*$param['hrgkgAll'])+($beratnormal*$param['subsidiangkut']+($beratnormal*$param['bonuskgAll']));
				if($param['bbnPajak']==1){
					$rppph= ($totalharga * ($param['prsnAll'] / 100));
				}else{
					$rppph=0;
				}
				$totdgnpph=$totalharga-$rppph;	
				$hargaperdata=$param['hrgkgAll'];
				$bonusperdata=$param['bonuskgAll'];
				$hargasubsidi=$param['subsidiangkut'];
				$dt="";
				if($param['bbnPajak']==0){
					$dt="selected";
				}
			}
			
			if($persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']]!=''){
				$prsn=$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']];
			} 
			$form.="
				<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center  id=klasifikasi".$no.">".$bar['notransaksi']."</td>
					<td align=center>".$bar['tanggal']."</td>
					<td>".$arrnmsupp[$bar['kodecustomer']]." <input type=hidden id=kdcust".$no." value='".$bar['kodecustomer']."' /></td>
					<td align=center>".$bar['nokendaraan']."</td>
					<td align=right>".number_format($bar['bruto'])."</td>
					<td align=right>".number_format($bar['tara'])."</td>
					<td align=right>".number_format($bar['netto'])."</td>
					<td align=right>".$bar['potongan']."</td>
					<td align=right id=beratnormal".$no.">".number_format($beratnormal)."</td>
					<td align=right><input type=text class=myinputtextnumber  onkeypress=\"return angka_doang(event);\"  style=width:50px id=harga".$no." onkeyup=getRup(".$no.") value='".$hargaperdata."' /></td>
					<td align=right><input type=text class=myinputtextnumber  onkeypress=\"return angka_doang(event);\"  style=width:50px id=bonus".$no." onkeyup=getRup(".$no.") value='".$bonusperdata."' /></td>
					<td align=right><input type=text class=myinputtextnumber  onkeypress=\"return angka_doang(event);\"  style=width:50px id=subsidi".$no." onkeyup=getRup(".$no.") value='".$hargasubsidi."' /></td>
					<td align=right><input type=text disabled class=myinputtextnumber  id=totalharga".$no." style=width:80px value='".number_format($totalharga,0)."' /></td>
					<td><select style=\"width:85px;\" id=optpph".$no." onchange=getRup(".$no.") >
					    <option value='1'>Di Tanggung</option>
					    <option value='0' ".$dt.">".$_SESSION['lang']['tidak']."</option></select></td>
					<td><input type=text placeholder='%' value='".$prsn."' id=persenpph".$no." size=5 class=myinputtextnumber maxlength=10 onkeypress=\"return angka_doang(event);\"   onkeyup=getRup(".$no.") style=\"width:50px;\"></td>
					<td><input style=\"width:100px;\" disabled type=text value='".number_format($rppph,0)."' id=rppph".$no."  class=myinputtextnumber  onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>					
					<td><input style=\"width:100px;\" disabled type=text value='".number_format($totdgnpph,0)."' id=totdgnpph".$no."  class=myinputtextnumber  onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
				</tr>";
				 
				$totBruto+=$bar['bruto'];
				$totTara+=$bar['tara'];
				$totNetto+=$bar['netto'];
				$totPot+=$bar['potongan'];
				$totBrtNormal+=$beratnormal;
				$totRppph+=$rppph;
				$totalharga2+=$totalharga;
				$totRupiah2+=$totdgnpph;
		}
		
		$form.="<tr class=rowcontent style='font-weight:bold'>
				<td colspan=5 style='text-align:right'>TOTAL</td>
				<td style='text-align:right'>".number_format($totBruto)."</td>
				<td style='text-align:right'>".number_format($totTara)."</td>
				<td style='text-align:right'>".number_format($totNetto)."</td>
				<td style='text-align:right'>".number_format($totPot)."</td>
				<td style='text-align:right'>".number_format($totBrtNormal)."</td>
				<td colspan=8></td>
			</tr>
		</table>";	
		$form.="<button class=mybutton onclick=saveDet(".$no.")>".$_SESSION['lang']['save']."</button></fieldset>
		        <input type=hidden id=jmlhRow value='".$no."' />";	
		echo $form;
	break;
	case'saveAll':
		#cek data
		$jmlhRow=count($_POST['beratnormal']);
		for($awl=0;$awl<$jmlhRow;$awl++){
			if(($_POST['harga'][$awl]=='')||($_POST['harga'][$awl]=='0')){
				exit('warning: '.$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg'].' '.$_SESSION['lang']['notifemptyzero']);
			}
			// if(($_POST['rppph'][$awl]=='')||($_POST['rppph'][$awl]=='0')){
				// exit('warning: '.$_SESSION['lang']['rp'].' '.$_SESSION['lang']['notifemptyzero']);
			// }
			$optSupp = array();
			$whrsup="b.kodetimbangan='".$param['kdcust'][$awl]."'";
			$str="select a.supplierid,a.namasupplier,b.kodetimbangan from ".$dbname.".log_5supplier a left join ".$dbname.".log_5suptimbangan b on a.supplierid=b.supplierid left join ".$dbname.".log_5supkelompok c on a.supplierid=c.supplierid where c.tipe='SUPPLIERTBS' and ".$whrsup."";
			$arr=fetchData($str);
			foreach($arr as $val)
			{
				$optSupp[$val['kodetimbangan']] = $val['namasupplier'];
			}
		    if(is_null($optSupp[$param['kdcust'][$awl]])||($optSupp[$param['kdcust'][$awl]]=='')){
		    	exit('warning: '.$_SESSION['lang']['supplier'].' '.$_SESSION['lang']['notifemptyzero']);	
		    }
        }
        $notransaksi="";
        if($param['notransaksi']!=''){
        	$notransaksi=$param['notransaksi'];
        }
        $sHo="select kodeorganisasi from ".$dbname.".organisasi where induk in (select induk from ".$dbname.".organisasi 
		where kodeorganisasi='".$param['unitmill']."') and tipe='HOLDING'";
		$qHo=$owlPDO->query($sHo) or die(print " Gagal: ".PDOException::getMessage());
		$qHo->setFetchMode(PDO::FETCH_ASSOC);
		$rowDt=$qHo->fetch();
		
        if($notransaksi==''){
        	#create notransaksi
			$notrans=tanggalsystem($param['tgldt'])."/HTGTBS/".$param['unitmill'];
			$sCek="select right(notransaksi,3) as nourut from ".$dbname.".keu_persediaantbs_ht where notransaksi like '%".$notrans."%' order by notransaksi desc";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_ASSOC);
			$rowNotrans=$qCek->fetch();
			$nourutdt=addZero((intval($rowNotrans['nourut'])+1),3);
			$notransaksi=$notrans."/".$nourutdt;
			#end create notransaksi
			$optTimbangan = makeOption($dbname,'log_5suptimbangan','kodetimbangan,supplierid',"kodetimbangan='".$param['suppId']."'");
			$whrsup="supplierid='".$optTimbangan[$param['suppId']]."'";
			$sSupp="select supplierid,namasupplier from ".$dbname.".log_5supplier where ".$whrsup;
			$rSupp=fetchData($sSupp);
			$kdSupp=$rSupp[0]['supplierid'];
			$cekIsi="select count(*) as data from ".$dbname.".keu_persediaantbs_ht a left join ".$dbname.".keu_persediaantbs_dt b
					 on a.notransaksi=b.notransaksi
			         where a.kodeunit='".$param['unitmill']."' and a.tanggal='".tanggalsystemn($param['tgldt'])."' and b.kodesupplier='".$kdSupp."'";
			//exit('warning :'.$cekIsi);
			$qCekDt=$owlPDO->query($cekIsi) or die(print " Gagal: ".PDOException::getMessage());
			$qCekDt->setFetchMode(PDO::FETCH_ASSOC);
			$rowCekDt=$qCekDt->fetch();
			if($rowCekDt['data']!='0'){
				exit('warning: Data Pada tanggal '.$param['tgldt'].' Untuk unit '.$param['unitmill'].' dan Supplier : '.$rSupp[0]['namasupplier'].' sudah ada');
			}
        }
		
		if($notransaksi!=''){
			$sDel="delete from ".$dbname.".keu_persediaantbs_ht where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($sDel);
				$sIns="insert into ".$dbname.".keu_persediaantbs_ht (`notransaksi`,`tanggal`,`kodeunit`,`updateby`,`kodeho`,`createby`,`createtime`) 
				       values ('".$notransaksi."','".tanggalsystem($param['tgldt'])."',
					   '".$param['unitmill']."','".$_SESSION['standard']['userid']."',
					   '".$rowDt['kodeorganisasi']."',
					   '".$_SESSION['standard']['userid']."',
						'".date('Y-m-d H:i')."')";
				try{
		            $owlPDO->exec($sIns); 
		            $sInsDet.="insert into ".$dbname.".keu_persediaantbs_dt  (`notransaksi`,`kodesupplier`,`klasifikasi`,`total_terima`,`harga_perkg`,`subsidi`,`beban_pajak`,`persenpajak`,`totalrupiah`,`totalrupiahbonus`,`rupiahpajak`,`bonus_perkg`) values ";
		            for($awl=0;$awl<$jmlhRow;$awl++){
						$optSupp = array();
						$whrsup="kodetimbangan='".$param['kdcust'][$awl]."'";
						$optSupp=makeOption($dbname,'log_5suptimbangan','kodetimbangan,supplierid',$whrsup);
						$arr=fetchData($str);
						foreach($arr as $key=>$val)
						{
							$optSupp[$val['supplierid']] = $val['namasupplier'];
						}

						$_POST['totalbonus'][$awl]=$_POST['beratnormal'][$awl]*$_POST['bonus'][$awl];
						$_POST['totalharga'][$awl]=$_POST['totalharga'][$awl]-$_POST['totalbonus'][$awl];

		            	if($awl==0){
							$sInsDet.="('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['totalbonus'][$awl]."','".$_POST['rppph'][$awl]."','".$_POST['bonus'][$awl]."')";	
						}else{
							$sInsDet.=",('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['totalbonus'][$awl]."','".$_POST['rppph'][$awl]."','".$_POST['bonus'][$awl]."')";	
						}
		            }
		            try{
						$owlPDO->exec($sInsDet); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
		        }
		        catch (PDOException $e){
		            echo "DB Error : " . $e->getMessage();
		            die();
		        } 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}else{
				$sIns="insert into ".$dbname.".keu_persediaantbs_ht (`notransaksi`,`tanggal`,`kodeunit`,`updateby`,`kodeho`) 
				       values ('".$notransaksi."','".tanggalsystem($param['tgldt'])."','".$param['unitmill']."','".$_SESSION['standard']['userid']."','".$rowDt['kodeorganisasi']."')";
				try{
		            $owlPDO->exec($sIns); 
		            $sInsDet.="insert into ".$dbname.".keu_persediaantbs_dt  (`notransaksi`,`kodesupplier`,`klasifikasi`,`total_terima`,`harga_perkg`,`subsidi`,`beban_pajak`,`persenpajak`,`totalrupiah`,`totalrupiahbonus`,`rupiahpajak`,`bonus_perkg`) values ";
		            for($awl=0;$awl<$jmlhRow;$awl++){
						$optSupp = array();
						$whrsup="kodetimbangan='".$param['kdcust'][$awl]."'";
						$optSupp=makeOption($dbname,'log_5suptimbangan','kodetimbangan,supplierid',$whrsup);
						$arr=fetchData($str);
						foreach($arr as $val)
						{
							$optSupp[$val['supplierid']] = $val['namasupplier'];
						}

						$_POST['totalbonus'][$awl]=$_POST['beratnormal'][$awl]*$_POST['bonus'][$awl];
						$_POST['totalharga'][$awl]=$_POST['totalharga'][$awl]-$_POST['totalbonus'][$awl];

						if($awl==0){
							$sInsDet.="('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['totalbonus'][$awl]."','".$_POST['rppph'][$awl]."','".$_POST['bonus'][$awl]."')";	
						}else{
							$sInsDet.=",('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['totalbonus'][$awl]."','".$_POST['rppph'][$awl]."','".$_POST['bonus'][$awl]."')";	
						}
		            }
		            try{
						$owlPDO->exec($sInsDet); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
		        }catch (PDOException $e){
		            echo "DB Error : " . $e->getMessage();
		            die();
		        }
		}	
		
	break;
	case'loaddata':
		#cek jabatan
		$sCkJbtn="select count(jabatan) as itung from ".$dbname.".setup_posting where jabatan='".$_SESSION['empl']['kodejabatan']."' and kodeaplikasi='keuangan'";
		//echo $sCkJbtn;
		$qCkJbtn=$owlPDO->query($sCkJbtn) or die(print " Gagal: ".PDOException::getMessage());
		$qCkJbtn->setFetchMode(PDO::FETCH_ASSOC);
		$rCkJbtn=$qCkJbtn->fetch();
		$where="kodeho<>''";
		if($param['millcode']!=''){
			$where.=" and kodeunit='".$param['millcode']."'";
		}
		if($param['tglcari']!=''){
			$where.=" and tanggal='".tanggalsystemn($param['tglcari'])."'";
		}
		if($param['jurnalId']!=''){
			$where.=" and jurnal='".$param['jurnalId']."'";
		}

        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select count(distinct notransaksi) as jmlhrow from ".$dbname.".keu_persediaantbs_vw where ".$where." 
              group by notransaksi  order by tanggal desc ";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		//$jlhbrs=owlBaris($res);	
		$jlhbrs=$res->rowCount();
		
		
	 
        $no=0;
		$no=$maxdisplay;
     //    $str="SELECT notransaksi,tanggal,updateby,postingby,sum(rupiahjurnal) as totbyr,sum(totalrupiah) as rp,
			  // sum(rupiahpajakditanggung) as rppajakdtngg,sum(rupiahpajaktdkditanggung) as rppajaktdkdtngg,kodeunit,jurnal,sum(total_terima) as kgtbs,kodesupplier,
			  // harga_perkg,subsidi,beban_pajak,persenpajak
     //          from ".$dbname.".keu_persediaantbs_vw where kodeho = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
     //          group by notransaksi   order by tanggal desc   limit ".$offset.",".$limit."";
        $str="SELECT notransaksi,tanggal,updateby,postingby,sum(rupiahjurnal) as totbyr,sum(totalrupiah) as rp,sum(totalrupiahbonus) as rpbonus,
			  sum(rupiahpajakditanggung) as rppajakdtngg,sum(rupiahpajaktdkditanggung) as rppajaktdkdtngg,kodeunit,jurnal,sum(total_terima) as kgtbs,kodesupplier,
			  harga_perkg,subsidi,beban_pajak,persenpajak,bonus_perkg,pengajuanbonus
              from ".$dbname.".keu_persediaantbs_vw where ".$where." 
              group by notransaksi   order by tanggal desc   limit ".$offset.",".$limit."";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $optNmKary=array();
            $optNmKary2=array();
            if(intval($bar['updateby'])!=0){
            	$whr="karyawanid='".$bar['updateby']."'";
            	$optNmKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
            }
            if(intval($bar['postingby'])!=0){
            	$whr="karyawanid='".$bar['postingby']."'";
            	$optNmKary2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
            }
			if($bar['beban_pajak']=='0')
			{
				$rppajak = 0;
				$rpbayar = $bar['rp']+$bar['rpbonus'];
			}
			else
			{
				$rppajak = round(($bar['rp']*(100/(100-$bar['persenpajak'])))*$bar['persenpajak']/100);
				$rpbayar = $bar['rp']+ $bar['rpbonus'] + $rppajak;
			}
			
            $whrsup=" a.supplierid='".$bar['kodesupplier']."'";
            

            $optSupp=makeOption($dbname,"log_5suptimbangan","supplierid,kodetimbangan","supplierid='".$bar['kodesupplier']."'");
            $sNmSup="select namasupplier from ".$dbname.".log_5supplier  where supplierid='".$bar['kodesupplier']."'";
            $rNmSup=fetchData($sNmSup);
            

            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodeunit']."</td>";
            $tab.="<td align=center>".$bar['notransaksi']."</td>";
            $tab.="<td align=center>".$rNmSup[0]['namasupplier']."</td>";
			$tab.="<td align=center>".$bar['tanggal']."</td>";
			
			$tab.="<td align=right>".number_format($bar['kgtbs'],0)."</td>";
			$tab.="<td align=right>".number_format($bar['rp'],0)."</td>";
			$tab.="<td align=right>".number_format($bar['rpbonus'],0)."</td>";
			$tab.="<td align=right>".number_format($rppajak,0)."</td>";
			$tab.="<td align=right>".number_format($rpbayar,0)."</td>";
			$tab.="<td align=left>".$optNmKary[$bar['updateby']]."</td>";
			$tab.="<td align=left>".$optNmKary2[$bar['postingby']]."</td>";
            $tab.="
            <td align=center>";
			if($bar['jurnal']==1){
				$tab.="
					<img src=images/skyblue/posted.png class=zImgOffBtn title='Posted');\">  
					<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detaildt('".$bar['notransaksi']."','".$bar['kodeunit']."','".$bar['tanggal']."','".$optSupp[$bar['kodesupplier']]."','event');\"> ";         
			}
			else{
				$postdt="";
				if($rCkJbtn['itung']==1){
					$postdt=" style='cursor:pointer;' onclick=\"posting('".$bar['notransaksi']."');\"";
				}
				$whrdt="kodeunit='".$bar['kodeunit']."'";
    			$optRegional2=makeOption($dbname,'bgt_regional_assignment','kodeunit,regional',$whrdt);
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                onclick=\"edit('".$bar['notransaksi']."','".$optRegional2[$bar['kodeunit']]."','".$bar['tanggal']."','".tanggalnormal($bar['tanggal'])."','".$bar['kodeunit']."','".$optSupp[$bar['kodesupplier']]."','".$bar['harga_perkg']."','".$bar['subsidi']."','".$bar['beban_pajak']."','".$bar['persenpajak']."','".$bar['bonus_perkg']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['notransaksi']."');\">
                <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detaildt('".$bar['notransaksi']."','".$bar['kodeunit']."','".$bar['tanggal']."','".$optSupp[$bar['kodesupplier']]."','event');\">         
                <img src=images/skyblue/posting.png class=zImgBtn title='Posting' ".$postdt."> ";
			}
			
			/*
			if ($bar['rpbonus']>0 && $bar['pengajuanbonus']=='0') {
				$tab.="<img src=images/icons/04/16/09.png class=resicon class=zImgBtn height='30'  title='Submitted' onclick=\"formapp('".$bar['notransaksi']."','".$bar['kodeunit']."');\" >";
			}else if($bar['rpbonus']>0 && $bar['pengajuanbonus']=='9'){
				$tab.="<img src=images/icons/04/16/04.png class=zImgOffBtn title='Submitted'>";
			}else if($bar['rpbonus']>0 && $bar['pengajuanbonus']=='3'){
				$tab.="<img src=images/icons/04/16/08.png class=zImgOffBtn title='Rejected'>";
			}else if($bar['rpbonus']>0 && $bar['pengajuanbonus']=='1'){
				$tab.="<img src=images/icons/04/16/03.png class=zImgOffBtn title='Approved'>";				
			}
			*/

            $tab.="</td>";
            $tab.="</tr>";
        }
        $totrows=ceil($jlhbrs/$limit);

        if($totrows==0){
                $totrows=1;
        }
        $isiRow='';
        for($er=1;$er<=$totrows;$er++){
                $sel = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd="
            <tr><td colspan=13 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
	break;

	case'formapp':
		$countApp = getCountApproval($jenisApp,$unit);
		$tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
		for($i=1;$i<=$countApp;$i++)
		{
			$optpersetujuan="";
			$listApp = listApprove($i,$jenisApp,$unit);
			foreach($listApp as $key=>$val)
			{
				$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
			}
			$tab.="<tr class='rowcontent'>";
			$tab.="<td>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
			$tab.="<td>:</td>";
			$tab.="<td>
				<select id='persetujuan".$i."' style=\"width:205px;\">".$optpersetujuan."</select>
			</td>";
			$tab.="</tr>";
		}
			
		$tab.="<tr class=rowcontent>
				<td colspan=2></td>
				<td style='text-align:left'>
					<button class=mybutton onclick=Submitted('".$param['notransaksi']."','".$unit."','".$countApp."')>Simpan</button>
				</td>
			</tr>
		</table>";
		echo $tab;
	break;

	case 'Submitted':
        $strht="update ".$dbname.".keu_persediaantbs_ht set pengajuanbonus='9' where notransaksi='".$param['notransaksi']."'";             
        try
        {
            $owlPDO->exec($strht);

            $listpersetujuan=$_POST['persetujuan'];
			foreach($listpersetujuan as $key=>$val)
			{
				$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$key."' and kodeunit='".$unit."'";
				$res=fetchData($str);
				$tipeapp = $res[0]['tipe'];
				$departemenapp = $res[0]['departemen'];
				$tipekaryawanapp = $res[0]['tipekaryawan'];
				$jabatanapp = $res[0]['jabatan'];

				if ($key=='1') {
					$status=0;
				}else{
					$status=9;
				}
				
				if($tipeapp=='1'){
					if($departemenapp!=''){
						$str="select * from ".$dbname.".datakaryawan where bagian='".$departemenapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenisApp."','".$key."','".$valx['karyawanid']."','".$status."')";
							$owlPDO->exec($str);
						}
					}
					if($tipekaryawanapp!=''){
						$str="select * from ".$dbname.".datakaryawan where tipekaryawan='".$tipekaryawanapp."' and bagian='BOD'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenisApp."','".$key."','".$valx['karyawanid']."','".$status."')";
							$owlPDO->exec($str);
						}
					}
					if($jabatanapp!='0'){
						$str="select * from ".$dbname.".datakaryawan where kodejabatan='".$jabatanapp."'";
						$res=fetchdata($str);
						foreach($res as $keyx=>$valx){
							$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenisApp."','".$key."','".$valx['karyawanid']."','".$status."')";
							$owlPDO->exec($str);
						}
					}
				}else{
					$str="insert into ".$dbname.".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('".$param['notransaksi']."','".$jenisApp."','".$key."','".$listpersetujuan[$key]."','".$status."')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						echo " Gagal," . addslashes($e->getMessage());
					}
				}
			}
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

	case'deleteData':
		$sDel="delete from ".$dbname.".keu_persediaantbs_ht where notransaksi='".$param['notransaksi']."'";
		try{
			$owlPDO->exec($sDel); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	case'detaildata':
	$arrStat=array("0"=>$_SESSION['lang']['tidak'],"1"=>"Di Tanggung");
	$form.="<link rel=stylesheet type=text/css href=style/generic.css>";
	$form.="<script language=javascript1.2 src='js/generic.js'></script>";
	$form.="<script language=javascript1.2 src='js/keu_penbytbs.js'></script>";
		$form.="<img class=\"zImgBtn\" src=\"images/skyblue/excel.jpg\" style=\"cursor:pointer\" onclick=\"printData('".$param['notransaksi']."','".$param['millcode']."','".$param['tglnormal']."','".$param['suppId']."',event)\" title=\"Print XLS\">";
		$form.="<fieldset><legend>".$_SESSION['lang']['detail']."</legend>";
		$form.="<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:100%>
                <thead>
				<tr class=rowheader>
					<td rowspan=2 align=center>".$_SESSION['lang']['nomor']."</td>
					<!--<td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['supplier']."</td>-->
					<td rowspan=2 align=center>".$_SESSION['lang']['noTiket']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['kendaraan']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notifbrutto']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['beratkosong']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['beratBersih']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['potongankg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['diterima']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['bonus']."/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>Subsidi Angkut/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." ke ".$_SESSION['lang']['supplier']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." bonus ke ".$_SESSION['lang']['supplier']."</td>
					<td colspan=3 align=center>Beban Pajak PPh 22</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." ".$_SESSION['lang']['all']."</td>
				</tr><tr>
				<td align=center align=center>".$_SESSION['lang']['status']."</td>
				<td align=center align=center>".$_SESSION['lang']['persen']."</td>
				<td align=center align=center>".$_SESSION['lang']['rp']."</td>
				</tr></thead>";
		$totbonus=0;
		if($param['notransaksi']!=''){
			$sData="select * from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$param['notransaksi']."'";
			$qData=fetchdata($sData);
			foreach($qData as $lstData){
				$whrsup="a.supplierid='".$lstData['kodesupplier']."'";
				$optSupp = array();
				// $whrsup="b.kodetimbangan='".$param['kdcust'][$awl]."'";
				$str="select a.supplierid,a.namasupplier,b.kodetimbangan from ".$dbname.".log_5supplier a left join ".$dbname.".log_5suptimbangan b on a.supplierid=b.supplierid left join ".$dbname.".log_5supkelompok c on a.supplierid=c.supplierid where c.tipe='SUPPLIERTBS' and ".$whrsup."";
				$arr=fetchData($str);
				foreach($arr as $val)
				{
					$optSupp[$val['supplierid']] = $val['kodetimbangan'];
				}
            	$suppId=$optSupp[$lstData['kodesupplier']];
				$hargadt[$suppId.$lstData['klasifikasi']]=$lstData['harga_perkg'];
				$bonusdt[$suppId.$lstData['klasifikasi']]=$lstData['bonus_perkg'];
				$subsididt[$suppId.$lstData['klasifikasi']]=$lstData['subsidi'];
				$persenpajak[$suppId.$lstData['klasifikasi']]=$lstData['persenpajak'];
				$bbnPajak[$suppId.$lstData['klasifikasi']]=$lstData['beban_pajak'];
				$pjkDitanggung[$suppId.$lstData['klasifikasi']]=$lstData['rupiahpajakditanggung'];
				$pjkTdkDitanggung[$suppId.$lstData['klasifikasi']]=$lstData['rupiahpajaktdkditanggung'];
				$totRupiah[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiah'];
				$totRupiahbonus[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiahbonus'];
				$totbonus+=$lstData['totalrupiahbonus'];
			}
		}
		$str=" select count(*) as kendaraan,left(tanggal,10) as tanggal,sum(beratmasuk) as bruto,
				sum(beratkeluar) as tara,sum(beratbersih) as netto,sum(kgpotsortasi) as potongan,
				kodecustomer,notransaksi,nokendaraan from ".$dbname.".pabrik_timbangan 
				where kodebarang='40000003' and intex=0 and left(tanggal,10)='".$param['tglnormal']."' and millcode='".$param['millcode']."' and kodecustomer='".$param['suppId']."'
				group by kodecustomer,notransaksi ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			$bar['id_klasifikasi']=$bar['notransaksi'];
			$beratnormal=$bar['netto']-$bar['potongan'];
			if($param['notransaksi']!=''){
				$totalharga=$totRupiah[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$totalbonus=$totRupiahbonus[$bar['kodecustomer'].$bar['id_klasifikasi']];
				
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==1){
					$rppph = (($totalharga*(100/(100-$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']])))*$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']]/100);
					// $rppph=$pjkDitanggung[$bar['kodecustomer'].$bar['id_klasifikasi']];	
					$totdgnpph=$totalharga+$totalbonus+$rppph;
				}else{
					$rppph=$pjkTdkDitanggung[$bar['kodecustomer'].$bar['id_klasifikasi']]*(-1);	
					$totdgnpph=$totalharga+$totalbonus;
					$totalharga=$totalharga+$totalbonus-$rppph;
					// $totalharga=$totalharga-$rppph;
				}

			}
			$prsn="0.5";
			if($persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']]!=''){
				$prsn=$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']];
			} 
			$form.="
				<tr class=rowcontent>
					<td align=center>".$no."</td>
					<!--<td align=center>".$bar['tanggal']."</td>
					<td>".$arrnmsupp[$bar['kodecustomer']]." <input type=hidden id=kdcust".$no." value='".$bar['kodecustomer']."' /></td>-->
					<td id=klasifikasi".$no.">".$bar['id_klasifikasi']."</td>
					<td>".$bar['nokendaraan']."</td>
					<td align=right>".number_format($bar['bruto'])."</td>
					<td align=right>".number_format($bar['tara'])."</td>
					<td align=right>".number_format($bar['netto'])."</td>
					<td align=right>".$bar['potongan']."</td>
					<td align=right id=beratnormal".$no.">".number_format($beratnormal)."</td>
					<td align=right>".$hargadt[$bar['kodecustomer'].$bar['id_klasifikasi']]."</td>
					<td align=right>".$bonusdt[$bar['kodecustomer'].$bar['id_klasifikasi']]."</td>
					<td align=right>".$subsididt[$bar['kodecustomer'].$bar['id_klasifikasi']]."</td>
					<td align=right>".number_format($totalharga,2)."</td>
					<td align=right>".number_format($totalbonus,2)."</td>
					<td>".$arrStat[$bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]]."</td>
					<td align=right>".$prsn."</td>
					<td align=right>".number_format($rppph,2)."</td>					
					<td align=right>".number_format($totdgnpph,2)."</td>
				</tr>";
				$totKendaraan+=$bar['kendaraan'];
				$totBruto+=$bar['bruto'];
				$totTara+=$bar['tara'];
				$totNetto+=$bar['netto'];
				$totPot+=$bar['potongan'];
				$totBrtNormal+=$beratnormal;
				if($rppph>0){
					$totRppph+=$rppph;	
				}
				$totalharga2+=$totalharga;
				$totalbonus2+=$totalbonus;
				$totRupiah2+=$totdgnpph;
		}
		@$hrg=$totalharga2/$totBrtNormal;

		$form.="<tr>
					<td colspan=3>".$param['tglnormal']."<br />Supplier : ".$arrnmsupp[$param['suppId']]."</td>
					<td align=right>".number_format($totBruto)."</td>
					<td align=right>".number_format($totTara)."</td>
					<td align=right>".number_format($totNetto)."</td>
					<td align=right>".$totPot."</td>
					<td align=right id=beratnormal".$no.">".number_format($totBrtNormal)."</td>
					<td align=right>&nbsp;</td>
					<td align=right>&nbsp;</td>
					<td align=right>&nbsp;</td>
					<td align=right>".number_format($totalharga2,2)."</td>
					<td align=right>".number_format($totalbonus2,2)."</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align=right>".number_format($totRppph,2)."</td>					
					<td align=right>".number_format($totRupiah2,2)."</td>
				</tr>";
		$form.="</table><br><br>";

		if ($totbonus>0) {
			$koderorg=$param['millcode'];
			$countApprove = getCountApproval($jenisApp,$koderorg);
			
			$form.="<div style='width:auto;overflow:auto;'>
				<legend style='font-weight:bold'>".$_SESSION['lang']['list']." Persetujuan Bonus</legend>
				<table border=0 cellspacing=1 class=sortable>
				<thead>
				<tr style='font-weight:bold'>";
					for($i=1;$i<=$countApprove;$i++)
					{
						$form.="<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
					}
				$form.="
				</tr>
				</thead>
				<tbody>";	

				$form.="<tr class=rowcontent>";
				$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"3"=>$_SESSION['lang']['ditolak']);
				for($i=1;$i<=$countApprove;$i++)
				{
					$arrApp = detailApprove($i,$param['notransaksi'],$jenisApp);
					if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00')
					{
						$tngl='';
					}
					else
					{
						$tngl=tanggalnormal($arrApp['tanggal']);
					}

					if ($arrApp['status']=='0') {
						$str="select * from ".$dbname.".setup_approval where jenispersetujuan='".$jenisApp."' and level='".$i."' and kodeunit='".$koderorg."'";
						$res=fetchData($str);
						$tipeapp = $res[0]['tipe'];
						$departemenapp = $res[0]['departemen'];
						$tipekaryawanapp = $res[0]['tipekaryawan'];

						if ($tipeapp=='1' && $tipekaryawanapp!='') {
							$arrApp['nama']='Direksi';
						}
					}
					
					if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0))
					{
						$form.="<td>".$arrApp['nama']."
							<br />".$arrHsl[$arrApp['status']]."
							<br>".$tngl."
						</td>";
					}
					else
					{
						$form.="<td>&nbsp;</td>";
					}
				}
				
			$form.="</tbody>
			</table><br />";
		}

		echo $form;
	break;
	case'excel':
	$arrStat=array("0"=>$_SESSION['lang']['tidak'],"1"=>"Di Tanggung");
	
		$form.="<table cellpading=1 cellspacing=1 border=1 class=sortable style=width:100%>
                <thead style=\"background-color:#EEEFFF\">
				<tr class=rowheader>
					<td rowspan=2 align=center>".$_SESSION['lang']['nomor']."</td>
					<!--<td rowspan=2 align=center>".$_SESSION['lang']['tanggal']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['supplier']."</td>-->
					<td rowspan=2 align=center>".$_SESSION['lang']['noTiket']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['kendaraan']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notifbrutto']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['beratkosong']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['beratBersih']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['potongankg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['diterima']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['bonus']."/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>Subsidi Angkut/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." ke ".$_SESSION['lang']['supplier']."</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." bonus ke ".$_SESSION['lang']['supplier']."</td>
					<td colspan=3 align=center>Beban Pajak PPh 22</td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." ".$_SESSION['lang']['all']."</td>
				</tr><tr>
				<td align=center align=center>".$_SESSION['lang']['status']."</td>
				<td align=center align=center>".$_SESSION['lang']['persen']."</td>
				<td align=center align=center>".$_SESSION['lang']['rp']."</td>
				</tr></thead>";
		if($param['notransaksi']!=''){
			$sData="select * from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$param['notransaksi']."'";
			$qData=fetchdata($sData);
			foreach($qData as $lstData){
				$whrsup="a.supplierid='".$lstData['kodesupplier']."'";
				$optSupp = array();
				// $whrsup="b.kodetimbangan='".$param['kdcust'][$awl]."'";
				$str="select a.supplierid,a.namasupplier,b.kodetimbangan from ".$dbname.".log_5supplier a left join ".$dbname.".log_5suptimbangan b on a.supplierid=b.supplierid left join ".$dbname.".log_5supkelompok c on a.supplierid=c.supplierid where c.tipe='SUPPLIERTBS' and ".$whrsup."";
				$arr=fetchData($str);
				foreach($arr as $val)
				{
					$optSupp[$val['supplierid']] = $val['kodetimbangan'];
				}
            	$suppId=$optSupp[$lstData['kodesupplier']];
				$hargadt[$suppId.$lstData['klasifikasi']]=$lstData['harga_perkg'];
				$bonusdt[$suppId.$lstData['klasifikasi']]=$lstData['bonus_perkg'];
				$subsididt[$suppId.$lstData['klasifikasi']]=$lstData['subsidi'];
				$persenpajak[$suppId.$lstData['klasifikasi']]=$lstData['persenpajak'];
				$bbnPajak[$suppId.$lstData['klasifikasi']]=$lstData['beban_pajak'];
				$pjkDitanggung[$suppId.$lstData['klasifikasi']]=$lstData['rupiahpajakditanggung'];
				$pjkTdkDitanggung[$suppId.$lstData['klasifikasi']]=$lstData['rupiahpajaktdkditanggung'];
				$totRupiah[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiah'];
				$totRupiahbonus[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiahbonus'];
			}
		}
		$str=" select left(tanggal,10) as tanggal,sum(beratmasuk) as bruto,
				sum(beratkeluar) as tara,sum(beratbersih) as netto,sum(kgpotsortasi) as potongan,
				kodecustomer,notransaksi,nokendaraan from ".$dbname.".pabrik_timbangan 
				where kodebarang='40000003' and intex=0 and left(tanggal,10)='".$param['tglnormal']."' and millcode='".$param['millcode']."' and kodecustomer='".$param['suppId']."'
				group by kodecustomer,notransaksi  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			$beratnormal=$bar['netto']-$bar['potongan'];
			$bar['id_klasifikasi']=$bar['notransaksi'];
			if($param['notransaksi']!=''){

				$totalharga=$totRupiah[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$totalbonus=$totRupiahbonus[$bar['kodecustomer'].$bar['id_klasifikasi']];
				
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==1){
					$rppph = (($totalharga*(100/(100-$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']])))*$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']]/100);
					// $rppph=$pjkDitanggung[$bar['kodecustomer'].$bar['id_klasifikasi']];	
					$totdgnpph=$totalharga+$totalbonus+$rppph;
				}else{
					$rppph=$pjkTdkDitanggung[$bar['kodecustomer'].$bar['id_klasifikasi']]*(-1);	
					$totdgnpph=$totalharga+$totalbonus;
					$totalharga=$totalharga+$totalbonus-$rppph;
					// $totalharga=$totalharga-$rppph;
				}
				
			}
			$prsn="0.5";
			if($persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']]!=''){
				$prsn=$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']];
			} 
			$form.="
				<tr class=rowcontent>
					<td align=center>".$no."</td>
					<!--<td align=center>".$bar['tanggal']."</td>
					<td>".$arrnmsupp[$bar['kodecustomer']]." <input type=hidden id=kdcust".$no." value='".$bar['kodecustomer']."' /></td>-->
					<td id=klasifikasi".$no.">".$bar['id_klasifikasi']."</td>
					<td align=center>".$bar['nokendaraan']."</td>
					<td align=right>".number_format($bar['bruto'])."</td>
					<td align=right>".number_format($bar['tara'])."</td>
					<td align=right>".number_format($bar['netto'])."</td>
					<td align=right>".$bar['potongan']."</td>
					<td align=right id=beratnormal".$no.">".number_format($beratnormal)."</td>
					<td align=right>".$hargadt[$bar['kodecustomer'].$bar['id_klasifikasi']]."</td>
					<td align=right>".$bonusdt[$bar['kodecustomer'].$bar['id_klasifikasi']]."</td>
					<td align=right>".$subsididt[$bar['kodecustomer'].$bar['id_klasifikasi']]."</td>
					<td align=right>".number_format($totalharga,2)."</td>
					<td align=right>".number_format($totalbonus,2)."</td>
					<td>".$arrStat[$bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]]."</td>
					<td align=right>".$prsn."</td>
					<td align=right>".number_format($rppph,2)."</td>					
					<td align=right>".number_format($totdgnpph,2)."</td>
				</tr>";
				$totKendaraan+=$bar['kendaraan'];
				$totBruto+=$bar['bruto'];
				$totTara+=$bar['tara'];
				$totNetto+=$bar['netto'];
				$totPot+=$bar['potongan'];
				$totBrtNormal+=$beratnormal;
				$totRppph+=$rppph;
				$totalharga2+=$totalharga;
				$totRupiah2+=$totdgnpph;
		}
		@$hrg=$totalharga2/$totBrtNormal;
		$form.="	
				<tr>
				<td colspan=3 align=left>".$_SESSION['lang']['tanggal'].":".$param['tglnormal']."</td>
					<td align=right valing=top rowspan=2>".number_format($totBruto)."</td>
					<td align=right valing=top  rowspan=2>".number_format($totTara)."</td>
					<td align=right valing=top rowspan=2>".number_format($totNetto)."</td>
					<td align=right valing=top rowspan=2>".$totPot."</td>
					<td align=right valing=top  rowspan=2 id=beratnormal".$no.">".number_format($totBrtNormal)."</td>
					<td align=right valing=top rowspan=2>&nbsp;</td>
					<td align=right valing=top rowspan=2>&nbsp;</td>
					<td align=right valing=top rowspan=2>&nbsp;</td>
					<td align=right valing=top  rowspan=2>".number_format($totalharga2,2)."</td>
					<td rowspan=2>&nbsp;</td>
					<td rowspan=2>&nbsp;</td>
					<td align=right valing=top  rowspan=2>".number_format($totRppph,2)."</td>					
					<td align=right valing=top rowspan=2>".number_format($totRupiah2,2)."</td>
				</tr>
				<tr><td colspan=3>".$_SESSION['lang']['namasupplier']." : ".$arrnmsupp[$param['suppId']]."</td>
				</tr>
				";
		$form.="</table>";	
		$form.="<br>Note : Nilai bonus diberikan agar supplier TBS tidak kirim buah ke tempat lain.<br>";
		$form.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
		$tglSkrg=date("Ymd");
		$nop_="listBayar_".$param['millcode']."_".$param['tglnormal'];
		if(strlen($form)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$form))
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
	
	case'postData':	
	
		
		$sData="select * from ".$dbname.".keu_persediaantbs_ht where notransaksi='".$param['notransaksi']."'";
		$dataH = fetchData($sData);
		
		
		#cek periode unit
		$sPeriodeAk="select * from ".$dbname.".setup_periodeakuntansi 
		             where tanggalmulai>='".$dataH[0]['tanggal']."' and tanggalsampai<='".$dataH[0]['tanggal']."' 
		             and tutupbuku=0 and kodeorg='".$dataH[0]['kodeunit']."'";
		$rPeriodeAk=fetchdata($sPeriodeAk);
		if(count($rPeriodeAk)!=0){
			exit('warning :'.$_SESSION['lang']['notifperiode']);
		}
		
		$totTbs=0;
		$rpSupplier=array();
		$lstKlasifika=array();
		$lstSupp=array();
		$totPersediaan2=0;
		$totPajakDitanggung=0;
		$totPersediaan=0;
		$totPajak=0;
		$totalsubsidi=0;
		$suppId='';
		#nilai rupiah
		$str="select * from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage()."___".$str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$totalrupiah+=$bar['totalrupiah'];
			$kodesupplier=$bar['kodesupplier'];
		}
	
		/*
		
		DPP	:	100		D	K
							
		Pengakuan TBS					
		D	:	6410400	Pembelian	100	
		K	:	1115***	ACCRUAL		100
		
		*/
		
	   
		##NAMA SUPPLIER##
		$optsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$kodesupplier."' in 
			(select supplierid from ".$dbname.".log_5supkelompok where tipe like 'SUPPLIERTBS%')");
		$namasupplier = $optsup[$kodesupplier];

		#=== Cek if posted ===
		$error0 = "";
		if($dataH[0]['jurnal']==1) {
		    $error0 .= $_SESSION['lang']['errisposted'];
		}
		if($error0!='') {
		    echo "Data Error :\n".$error0;
		    exit;
		}
		#====cek periode
		$optTglAcc=makeOption($dbname,'setup_periodeakuntansi','kodeorg,tanggalmulai',"kodeorg='".$dataH[0]['kodeunit']."'");
		$tgl = str_replace("-","",$dataH[0]['tanggal']);
		
		if(tanggalsystem(tanggalnormal($optTglAcc[$dataH[0]['kodeorg']]))>$tgl){
			exit('Error:Date beyond active period'.tanggalsystem(tanggalnormal($optTglAcc[$dataH[0]['kodeorg']])));
		}
		
		#====notransaksi jurnal akun debet serta kredit dari parameter jurnal
		$kodejurnal="INVTB";
		$optInduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$dataH[0]['kodeunit']."'");
		$whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".$optInduk[$dataH[0]['kodeunit']]."'";
	    $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNoindukph);
	    $noKon = fetchData($query);
	    $tmpC = $noKon[0]['nokounter'];
	    $tmpC++;
	    $counterjurnal = addZero($tmpC,3);
	    $nojurnal = $tgl."/".$dataH[0]['kodeunit']."/".$kodejurnal."/".$counterjurnal;

		#akun debet serta krdit
	    $query2 = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',"jurnalid='".$kodejurnal."' and aktif=1");
	    $dtnoakun = fetchData($query2);
		
	    #=== Transform Data ===
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		
		# Prep Header
		$dataRes['header'] = array(
		    'nojurnal'=>$nojurnal,
		    'kodejurnal'=>$kodejurnal,
		    'tanggal'=>$dataH[0]['tanggal'],
		    'tanggalentry'=>date('Ymd'),
		    'posting'=>'0',
		    'totaldebet'=>($totalrupiah),
		    'totalkredit'=>($totalrupiah)*-1,
		    'amountkoreksi'=>'0',
		    'noreferensi'=>$dataH[0]['notransaksi'],
		    'autojurnal'=>'1',
		    'matauang'=>'IDR',
		    'kurs'=>'1',
		    'revisi'=>'0'
		);
		
		#= debet
		
		$noUrut=1;
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$dataH[0]['tanggal'],
			'nourut'=>$noUrut,
			'noakun'=>$dtnoakun[0]['noakundebet'],
			'keterangan'=>'Penerimaan TBS kode unit :'.$dataH[0]['kodeunit'].' dari supplier a/n '.$namasupplier.' pada tanggal '.$dataH[0]['tanggal'],
			'jumlah'=>$totalrupiah,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$dataH[0]['kodeunit'],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>'',
			'noreferensi'=>$dataH[0]['notransaksi'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>'',
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => '0000000001'
		);
		
		$noUrut++;
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$dataH[0]['tanggal'],
			'nourut'=>$noUrut,
			'noakun'=>$dtnoakun[0]['noakunkredit'],
			'keterangan'=>'Accrual Penerimaan TBS dari supplier '.$namasupplier.',pada tanggal :  '.$dataH[0]['tanggal'].' No Transaksi :'.$dataH[0]['notransaksi'],
			'jumlah'=>($totalrupiah)*-1,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$dataH[0]['kodeunit'],
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>$kodesupplier,
			'noreferensi'=>$dataH[0]['notransaksi'],
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>'',
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => '0000000001'
		);
		
		
		#=== Insert Data ===
		$errorDB = "";
		# Header
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
		try{$owlPDO->exec($queryH); }catch (PDOException $e) {$errorDB .= "Gagal :Header :". $e->getMessage() ; }
		# Detail
		if($errorDB=='') {
		    foreach($dataRes['detail'] as $key=>$dataDet) {
		        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		        try{$owlPDO->exec($queryD); }catch (PDOException $e) {$errorDB .= "Gagal :Detail: ".$key." ". $e->getMessage()."\n".$queryD ; }
			}
		}
		if($errorDB!=''){
			$sDel="delete from ".$dbname.".keu_jurnalht where nojurnal='".$nojurnal."'";
			try{$owlPDO->exec($sDel); }catch (PDOException $e) { $errorJRB .= "Rollback Parameter Jurnal Error :". $e->getMessage() ; }
			echo "DB Error :\n".$errorDB;
			
			#= delete tagihannya juga
			$strx="delete from ".$dbname.".keu_tagihanht where noinvoice in ('".$noinvoice1."','".$noinvoice2."')";
			try{$owlPDO->exec($strx); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
			exit();
		}
		#=== Switch Jurnal to 1 ===
	    # Cek if already posted
	    $queryJ = selectQuery($dbname,'keu_persediaantbs_ht',"jurnal","notransaksi='".$dataH[0]['notransaksi']."'");
	    $isJ = fetchData($queryJ);
	    if($isJ[0]['jurnal']==1) {
	        $errorDB .= "Data changed by other user";
	    } else {
	        $queryToJ = updateQuery($dbname,'keu_persediaantbs_ht',array('jurnal'=>1,'postingby'=>$_SESSION['standard']['userid']),
	            "notransaksi='".$dataH[0]['notransaksi']."'");
	        try{
	        	$owlPDO->exec($queryToJ);
	        	$sdel="delete from ".$dbname.".keu_5saldotbsnonramp where kodeorg='".$dataH[0]['kodeunit']."' and tanggal='".$dataH[0]['tanggal']."' and kodesupplier='".$suppId."'";
	        	try{
	        		// $owlPDO->exec($sdel);
	        		$sInsert="insert into  ".$dbname.".keu_5saldotbsnonramp (`kodeorg`,`kodesupplier`,`tanggal`,`fisik`,`hargasatuan`,`updateby`) 
		        	          values ('".$dataH[0]['kodeunit']."','".$suppId."','".$dataH[0]['tanggal']."','".$saldoBaru."','".$hargaRataBaru."','".$_SESSION['standard']['userid']."')";
		        	try{
		        		// $owlPDO->exec($sInsert);
		        		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpC+1),
					        $whereNoindukph);
					    $errCounter = "";
					    try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }

					    if($errCounter!="") {
					        $queryJRB = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpKonter[0]['nokounter']),
					            $whereNoindukph);
					        $errCounter = "";
					        try{$owlPDO->exec($queryJRB); }catch (PDOException $e) { $errorJRB .= "Rollback Parameter Jurnal Error :". $e->getMessage() ; }
					        echo "DB Error :\n".$errorJRB;
					        exit;
					    }
		        	}
		        	catch (PDOException $e) {
		        	$errorDB .= "Posting Flag Error". $e->getMessage() ; 
		        	}
	        	}
	        	catch (PDOException $e) {
	        	$errorDB .= "Posting Flag Error". $e->getMessage() ; 
	        	}
	        }
	        catch (PDOException $e) {
	        	$errorDB .= "Posting Flag Error". $e->getMessage() ; 
	        }
	    }
		
		
	break;
	

	
}
?>