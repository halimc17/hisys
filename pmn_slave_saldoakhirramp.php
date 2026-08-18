<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');



$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$ramp = checkPostGet('ramp','');
$saldo = checkPostGet('saldo','0');
$tanggal = tanggalsystem(checkPostGet('tgl',''));
$notrans = tanggalsystem(checkPostGet('notrans',''));
$arrnmsupp=  makeOption($dbname, 'log_5supplier', 'kodetimbangan,namasupplier');

if(isset($_POST['method'])){
	$param=$_POST;	
}else{
	$param=$_GET;
}


switch ($method) 
{
	case'getkoderamp':
		$optinduk = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
		
		$str="select distinct kode,kelompok from ".$dbname.".log_5klsupplier
			where kode like 'R".$optinduk[$unit]."%' and tipe='RAMP' order by kelompok ASC";
		$res=fetchdata($str);
		$optunit.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		foreach($res as $val)
		{
			if($ramp == $val['kode'])
			{
				$optunit.="<option value='".$val['kode']."' selected>".$val['kelompok']."</option>";	
			}
			else
			{
				$optunit.="<option value='".$val['kode']."'>".$val['kelompok']."</option>";
			}
		}
		echo $optunit;
	break;
	
	case'savehead':
	
		if($notrans=='')
		{
			$optramp = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$ramp."'");
			if($unit=='')
			{
				exit("warning : Unit harus dipilih.");
			}
			if($ramp=='')
			{
				exit("warning : Kode ramp harus dipilih");
			}
			
			$str = "select count(koderamp) as jumlah from ".$dbname.".pmn_saldoakhirramp where unit='".$unit."' and koderamp='".$ramp."' and tanggal='".$tanggal."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			
			if($bar['jumlah']>=1)
			{
				exit("error : Data untuk unit : ".$unit.", kode ramp : ".$ramp."(".$optramp[$ramp]."), tanggal : ".tanggalnormal($tanggal)." sudah ada disistem");
			}
			
			$str = "insert into ".$dbname.".pmn_saldoakhirramp values ('".$unit."','".$ramp."','".$tanggal."','".$saldo."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			try
			{
				$owlPDO->exec($str);
			}
			catch(PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
		else
		{
			$str = "update ".$dbname.".pmn_saldoakhirramp set saldoakhir='".$saldo."',updateby='".$_SESSION['standard']['userid']."' where unit='".$unit."' and koderamp='".$ramp."' and tanggal='".$tanggal."'";
			try
			{
				$owlPDO->exec($str);
			}
			catch(PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}
	break;
	
	case'getPabrik':
		$sReg="select distinct kodecustomer,namasupplier from ".$dbname.".pabrik_timbangan a left join
			   ".$dbname.".log_5supplier b on a.kodecustomer=b.kodetimbangan
			   where millcode='".$param['kdPabrik']."' and left(tanggal,10)='".tanggalsystemn($param['tgl'])."'
			   and intex=0 and kodebarang='40000003' and ramp=''";
		//exit ("error : $sReg");
		$resData=fetchdata($sReg);
		$optunit.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($resData as $isiData){
			if($param['kdcust']!=''){
				$optunit.="<option value='".$isiData['kodecustomer']."' ".($param['kdcust']==$isiData['kodecustomer']?'selected':'').">".$isiData['namasupplier']."</option>";	
			}else{
				$optunit.="<option value='".$isiData['kodecustomer']."'>".$isiData['namasupplier']."</option>";
			}
		}
		echo $optunit;
	break;
	case'detail':
		$str=" select notransaksi,left(tanggal,10) as tanggal,beratmasuk as bruto,
				beratkeluar as tara,beratbersih as netto,kgpotsortasi as potongan,
				kodecustomer,nokendaraan from ".$dbname.".pabrik_timbangan 
				where kodebarang='40000003' and intex=0 and left(tanggal,10)='".tanggalsystemn($param['tglnormal'])."' and millcode='".$param['millcode']."'
				and kodecustomer='".$param['suppId']."'";
				//echo $str;
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
					<td rowspan=2 align=center>".$_SESSION['lang']['notifbrutto']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['beratkosong']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['beratBersih']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['potongankg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['diterima']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['harga']."/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>Subsidi Angkut/".$_SESSION['lang']['kg']." </td>
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
            	$optSupp=makeOption($dbname,'log_5supplier','supplierid,kodetimbangan',$whrsup);
            	$suppId=$optSupp[$lstData['kodesupplier']];
				$hargadt[$suppId.$lstData['klasifikasi']]=$lstData['harga_perkg'];
				$subsididt[$suppId.$lstData['klasifikasi']]=$lstData['subsidi'];
				$persenpajak[$suppId.$lstData['klasifikasi']]=$lstData['persenpajak'];
				$bbnPajak[$suppId.$lstData['klasifikasi']]=$lstData['beban_pajak'];
				$totRupiah[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiah'];
			}
		}
		$str=" select notransaksi,left(tanggal,10) as tanggal,beratmasuk as bruto,
				beratkeluar as tara,beratbersih as netto,kgpotsortasi as potongan,
				kodecustomer,nokendaraan from ".$dbname.".pabrik_timbangan 
				where kodebarang='40000003' and intex=0 and left(tanggal,10)='".tanggalsystemn($param['tglnormal'])."' and millcode='".$param['millcode']."' and kodecustomer='".$param['suppId']."'";
				// echo $str;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			$beratnormal=$bar['netto']-$bar['potongan'];
			$prsn="0.25";

			if($param['notransaksi']!=''){
				$bar['id_klasifikasi']=$bar['notransaksi'];
				$totalharga=($beratnormal*$hargadt[$bar['kodecustomer'].$bar['id_klasifikasi']])+($beratnormal*$subsididt[$bar['kodecustomer'].$bar['id_klasifikasi']]);
				$rppph= ($totalharga * (100/(100-$persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']]))) - $totalharga;
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==1){
					$totdgnpph=$totalharga+$rppph;	
				}else{
					$totdgnpph=$totalharga-$rppph;
				}
				$hargaperdata=$hargadt[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$hargasubsidi=$subsididt[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$dt="";
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==0){
					$dt="selected";
				}
			}else{
				$totalharga=($beratnormal*$param['hrgkgAll'])+($beratnormal*$param['subsidiangkut']);
				$rppph= ($totalharga * (100/(100-$param['prsnAll']))) - $totalharga;
				if($param['bbnPajak']==1){
					$totdgnpph=$totalharga+$rppph;	
				}else{
					$totdgnpph=$totalharga-$rppph;
				}
				$hargaperdata=$param['hrgkgAll'];
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
				<td colspan=7></td>
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
			if(($_POST['rppph'][$awl]=='')||($_POST['rppph'][$awl]=='0')){
				exit('warning: '.$_SESSION['lang']['rp'].' '.$_SESSION['lang']['notifemptyzero']);
			}
			$whrsup="kodetimbangan='".$param['kdcust'][$awl]."'";
		    $optSupp=makeOption($dbname,'log_5supplier','kodetimbangan,supplierid',$whrsup);
		    if(is_null($optSupp[$param['kdcust'][$awl]])||($optSupp[$param['kdcust'][$awl]]=='')){
		    	exit('warning: '.$_SESSION['lang']['supplier'].' '.$_SESSION['lang']['notifemptyzero']);	
		    }
        }
        $notransaksi="";
        if($param['notransaksi']!=''){
        	$notransaksi=$param['notransaksi'];
        }
        $sHo="select kodeorganisasi from ".$dbname.".organisasi where induk in (select induk from ".$dbname.".organisasi where kodeorganisasi='".$param['unitmill']."') and tipe='HOLDING'";
		$qHo=$owlPDO->query($sHo) or die(print " Gagal: ".PDOException::getMessage());
		$qHo->setFetchMode(PDO::FETCH_ASSOC);
		$rowDt=$qHo->fetch();

        if($notransaksi==''){
        	#create notransaksi
			$notrans=tanggalsystem($param['tgldt'])."/HTGTBS/".$rowDt['kodeorganisasi'];
			$sCek="select right(notransaksi,3) as nourut from ".$dbname.".keu_persediaantbs_ht where notransaksi like '%".$notrans."%' order by notransaksi desc";
			$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$qCek->setFetchMode(PDO::FETCH_ASSOC);
			$rowNotrans=$qCek->fetch();
			$nourutdt=addZero((intval($rowNotrans['nourut'])+1),3);
			$notransaksi=$notrans."/".$nourutdt;
			#end create notransaksi
			$whrsup="kodetimbangan='".$param['suppId']."'";
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
				$sIns="insert into ".$dbname.".keu_persediaantbs_ht (`notransaksi`,`tanggal`,`kodeunit`,`updateby`,`kodeho`) 
				       values ('".$notransaksi."','".tanggalsystem($param['tgldt'])."','".$param['unitmill']."','".$_SESSION['standard']['userid']."','".$rowDt['kodeorganisasi']."')";
				try{
		            $owlPDO->exec($sIns); 
		            $sInsDet.="insert into ".$dbname.".keu_persediaantbs_dt  (`notransaksi`,`kodesupplier`,`klasifikasi`,`total_terima`,`harga_perkg`,`subsidi`,`beban_pajak`,`persenpajak`,`totalrupiah`,`rupiahpajak`) values ";
		            for($awl=0;$awl<$jmlhRow;$awl++){
		            	$whrsup="kodetimbangan='".$param['kdcust'][$awl]."'";
		            	$optSupp=makeOption($dbname,'log_5supplier','kodetimbangan,supplierid',$whrsup);
						if($awl==0){
							$sInsDet.="('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['rppph'][$awl]."')";	
						}else{
							$sInsDet.=",('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['rppph'][$awl]."')";	
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
		            $sInsDet.="insert into ".$dbname.".keu_persediaantbs_dt  (`notransaksi`,`kodesupplier`,`klasifikasi`,`total_terima`,`harga_perkg`,`subsidi`,`beban_pajak`,`persenpajak`,`totalrupiah`,`rupiahpajak`) values ";
		            for($awl=0;$awl<$jmlhRow;$awl++){
		            	$whrsup="kodetimbangan='".$param['kdcust'][$awl]."'";
		            	$optSupp=makeOption($dbname,'log_5supplier','kodetimbangan,supplierid',$whrsup);
						if($awl==0){
							$sInsDet.="('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['rppph'][$awl]."')";	
						}else{
							$sInsDet.=",('".$notransaksi."','".$optSupp[$_POST['kdcust'][$awl]]."','".$_POST['klasifikasi'][$awl]."',
										'".$_POST['beratnormal'][$awl]."','".$_POST['harga'][$awl]."','".$_POST['subsidi'][$awl]."','".$_POST['statusPajak'][$awl]."','".$_POST['persenpph'][$awl]."','".$_POST['totalharga'][$awl]."','".$_POST['rppph'][$awl]."')";	
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

		if($param['millcode']!=''){
			$where.=" and unit='".$param['millcode']."'";
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
        $str="select count(koderamp) as jmlhrow from ".$dbname.".pmn_saldoakhirramp where 1=1 ".$where." order by tanggal desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=$res->rowCount();
		
		$no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".pmn_saldoakhirramp where 1=1 ".$where." order by tanggal desc limit ".$offset.",".$limit."";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
            $no+=1;
            $optramp = makeOption($dbname,'log_5klsupplier','kode,kelompok',"kode='".$bar['koderamp']."'");
            $optnmkary = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['unit']."</td>";
			$tab.="<td align=center>".$bar['koderamp']." - ".$optramp[$bar['koderamp']]."</td>";
			$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td align=right>".number_format($bar['saldoakhir'],0)."</td>";
			$tab.="<td align=center>".$optnmkary[$bar['updateby']]."</td>";
            
			$strprd = "select tutupbuku from ".$dbname.".setup_periodeakuntansi where periode='".substr($bar['tanggal'],0,7)."' and kodeorg='".$bar['unit']."'";
			$resprd=$owlPDO->query($strprd) or die(print " Gagal: ".PDOException::getMessage());
			$resprd->setFetchMode(PDO::FETCH_ASSOC);
			$barprd=$resprd->fetch();
			
			$tab.="
            <td align=center>";
			if($barprd['tutupbuku']==1){
				$tab.="
					<img src=images/skyblue/posted.png class=zImgOffBtn title='Posted');\">";         
			}
			else
			{
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                onclick=\"edit('".$bar['unit']."','".$bar['koderamp']."','".tanggalnormal($bar['tanggal'])."','".number_format($bar['saldoakhir'],0)."','edit');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['unit']."','".$bar['koderamp']."','".tanggalnormal($bar['tanggal'])."');\">";
			}
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
            <tr><td colspan=11 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
	break;
	
	case'deleteData':
		$str="delete from ".$dbname.".pmn_saldoakhirramp where unit='".$unit."' and koderamp='".$ramp."' and tanggal='".$tanggal."'";
		try
		{
			$owlPDO->exec($str); 
		}
		catch(PDOException $e)
		{
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
					<td rowspan=2 align=center>Subsidi Angkut/".$_SESSION['lang']['kg']." </td>
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." ke ".$_SESSION['lang']['supplier']."</td>
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
				$whrsup="supplierid='".$lstData['kodesupplier']."'";
            	$optSupp=makeOption($dbname,'log_5supplier','supplierid,kodetimbangan',$whrsup);
            	$suppId=$optSupp[$lstData['kodesupplier']];
				$hargadt[$suppId.$lstData['klasifikasi']]=$lstData['harga_perkg'];
				$subsididt[$suppId.$lstData['klasifikasi']]=$lstData['subsidi'];
				$persenpajak[$suppId.$lstData['klasifikasi']]=$lstData['persenpajak'];
				$bbnPajak[$suppId.$lstData['klasifikasi']]=$lstData['beban_pajak'];
				$pjkDitanggung[$suppId.$lstData['klasifikasi']]=$lstData['rupiahpajakditanggung'];
				$pjkTdkDitanggung[$suppId.$lstData['klasifikasi']]=$lstData['rupiahpajaktdkditanggung'];
				$totRupiah[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiah'];
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
				
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==1){
					$rppph=$pjkDitanggung[$bar['kodecustomer'].$bar['id_klasifikasi']];	
					$totdgnpph=$totalharga+$rppph;
				}else{
					$rppph=$pjkTdkDitanggung[$bar['kodecustomer'].$bar['id_klasifikasi']]*(-1);	
					$totdgnpph=$totalharga;
					$totalharga=$totalharga-$rppph;
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
					<td align=right>".$subsididt[$bar['kodecustomer'].$bar['id_klasifikasi']]."</td>
					<td align=right>".number_format($totalharga,2)."</td>
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
					<td align=right>".number_format($totalharga2,2)."</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align=right>".number_format($totRppph,2)."</td>					
					<td align=right>".number_format($totRupiah2,2)."</td>
				</tr>";
		$form.="</table>";	
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
					<td rowspan=2 align=center>".$_SESSION['lang']['notiftotalbayar']." ke ".$_SESSION['lang']['supplier']."</td>
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
				$whrsup="supplierid='".$lstData['kodesupplier']."'";
            	$optSupp=makeOption($dbname,'log_5supplier','supplierid,kodetimbangan',$whrsup);
            	$suppId=$optSupp[$lstData['kodesupplier']];
				$hargadt[$suppId.$lstData['klasifikasi']]=$lstData['harga_perkg'];
				$persenpajak[$suppId.$lstData['klasifikasi']]=$lstData['persenpajak'];
				$bbnPajak[$suppId.$lstData['klasifikasi']]=$lstData['beban_pajak'];
				$totRupiah[$suppId.$lstData['klasifikasi']]=$lstData['totalrupiah'];
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
				$totalharga=$beratnormal*$hargadt[$bar['kodecustomer'].$bar['id_klasifikasi']];
				$rppph=($persenpajak[$bar['kodecustomer'].$bar['id_klasifikasi']]/100)*$totalharga;
				if($bbnPajak[$bar['kodecustomer'].$bar['id_klasifikasi']]==1){
					$totdgnpph=$totalharga+$rppph;	
				}else{
					$totdgnpph=$totalharga;
					$totalharga=$totalharga-$rppph;
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
					<td align=right>".number_format($totalharga,2)."</td>
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
		#cek apakah produksi sudah posting di tanggal kemarin
		#expl : tgl 1 by tbs sudah posting, produksi belum,
		#sehinggal tgl 2 tidak bisa posting untuk by tbs,
		#karena produksi di tgl 1 belum di posting
		$sData="select * from ".$dbname.".keu_persediaantbs_ht where notransaksi='".$param['notransaksi']."'";
		$dataH = fetchData($sData);
		
		#tanggal lalu
		$tglLalu=strtotime('-1 day',strtotime($dataH[0]['tanggal'])) ;
		$tglLalu=date("Y-m-d",$tglLalu);
		
		#cek periode unit
		$sPeriodeAk="select * from ".$dbname.".setup_periodeakuntansi 
		             where tanggalmulai>='".$dataH[0]['tanggal']."' and tanggalsampai<='".$dataH[0]['tanggal']."' 
		             and tutupbuku=0 and kodeorg='".$dataH[0]['kodeunit']."'";
		$rPeriodeAk=fetchdata($sPeriodeAk);
		if(count($rPeriodeAk)!=0){
			exit('warning :'.$_SESSION['lang']['notifperiode']);
		}
		#cek apakah ada pengolahan di tanggal sebelumnya
		$scek="select * from ".$dbname.".pabrik_produksi where kodeorg='".$dataH[0]['kodeunit']."' and tanggal='".$tglLalu."'";
		$rCek=fetchData($scek);
		if($rCek[0]['tbsdiolahnetto']!=0){
				$str="select count(*) as posting from ".$dbname.".keu_jurnaldt_vw where nojurnal like '%HPPTB%' 
					  and tanggal='".$tglLalu."' and kodeorg='".$dataH[0]['kodeunit']."'  ";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage()."___".$str);
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();	
					$posting=$bar['posting'];
					
				// if($posting==0 || $posting==''){
					// exit("Warning:Harap posting produksi dahulu untuk tanggal ".tanggalnormal($tglLalu)." ");
				// }	
		}else{
			#cek apakah ada pengolahan di tanggal sebelumnya
			// $scek="select * from ".$dbname.".pabrik_produksi where kodeorg='".$dataH[0]['kodeunit']."' and tanggal='".$tglLalu."' and posting=1";
			// $rCek=fetchData($scek);
			// if($rCek[0]['posting']==0){
			// 		exit("Warning:Harap posting produksi dahulu untuk tanggal ".tanggalnormal($tglLalu)." ");
			// }	
		}
		
			
		
		$totTbs=0;
		$rpSupplier=array();
		$lstKlasifika=array();
		$lstSupp=array();
		$totPersediaan2=0;
		$totPajakDitanggung=0;
		$totPersediaan=0;
		$totPajak=0;
		$suppId='';
		#nilai rupiah
		$sRupiah="select kodesupplier,klasifikasi,sum(rupiahbayar) as rpbayar,sum(totalrupiah) as totRupiah,beban_pajak,sum(rupiahpajak) as pajak,sum(total_terima) as kgtbs 
		          from ".$dbname.".keu_persediaantbs_vw where notransaksi='".$param['notransaksi']."'
		          group by kodesupplier,klasifikasi,beban_pajak";
		$rpData=fetchdata($sRupiah);
		foreach($rpData as $lstData){
			$totPersediaan2+=$lstData['totRupiah'];	
			if($lstData['beban_pajak']==0){
				$totPersediaan+=$lstData['totRupiah'];	
				$rpSupplier[$lstData['kodesupplier'].$lstData['klasifikasi']]+=$lstData['rpbayar'];
			}else{
				$totPajakDitanggung+=$lstData['pajak'];
				$totPersediaan+=$lstData['rpbayar'];	
				$rpSupplier[$lstData['kodesupplier'].$lstData['klasifikasi']]+=$lstData['totRupiah'];
			}
			if(strtolower(substr($lstData['klasifikasi'],0,1))!='x'){
				#untuk notiket berawalan x fisik tidak diakui
				$totTbs+=$lstData['kgtbs'];
			}
			$totPajak+=$lstData['pajak'];	
			$lstSupp[$lstData['kodesupplier']]=$lstData['kodesupplier'];
			$lstKlasifika[$lstData['klasifikasi']]=$lstData['klasifikasi'];
			$suppId=$lstData['kodesupplier'];
		}
		$saldoBaru=$totTbs;
		$hargaRataBaru=$totPersediaan2/$totTbs;

		#ambil saldo fisik terakhir dari keu_5saldotbs
		#dirubahkarena pada daw ada kasus memiliki ramp dan non ramp
		#untuk non ramp ambil disini
		// $sAkhir="select hargarata,fisik,tanggal,kodeunit,fisikolah from ".$dbname.".keu_5saldotbs where kodeunit='".$dataH[0]['kodeunit']."' and tanggal='".$tglLalu."' order by tanggal desc limit 1";
		// $rAkhir=fetchData($sAkhir);
		// if(is_null($rAkhir[0]['kodeunit'])){
		// 	$sCek="select * from ".$dbname.".pabrik_timbangan where left(tanggal,10)='".$tglLalu."' and millcode='".$dataH[0]['kodeunit']."'";
		// 	$rCek=fetchData($sCek);
		// 	if(count($rCek)!=0){
		// 		exit('warning: Harga Rata Hari Sebelumnya Kosong, silakan proses data tanggal : '.$tglLalu);	
		// 	}else{
		// 		$sAkhir2="select hargarata,fisikolah,fisik from ".$dbname.".keu_5saldotbs where kodeunit='".$dataH[0]['kodeunit']."' order by tanggal desc limit 1";
		// 		$rAkhir2=fetchData($sAkhir2);
		// 	}
			
		// }
		
		// if($rAkhir2[0]['fisikolah']!=0){
		// 	$saldoBaru=($rAkhir2[0]['fisik']-$rAkhir2[0]['fisikolah'])+$totTbs;
		// 	@$hargaRataBaru=(($rAkhir2[0]['hargarata']*($rAkhir2[0]['fisik']-$rAkhir2[0]['fisikolah']))+$totPersediaan2)/$saldoBaru;
		// }else{
		// 	$saldoBaru=($rAkhir[0]['fisik']-$rAkhir[0]['fisikolah'])+$totTbs;
		// 	@$hargaRataBaru=(($rAkhir[0]['hargarata']*($rAkhir[0]['fisik']-$rAkhir[0]['fisikolah']))+$totPersediaan2)/$saldoBaru;
		// }
		
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
		    'totaldebet'=>$totPersediaan,
		    'totalkredit'=>$totPersediaan*(-1),
		    'amountkoreksi'=>'0',
		    'noreferensi'=>$dataH[0]['notransaksi'],
		    'autojurnal'=>'1',
		    'matauang'=>'IDR',
		    'kurs'=>'1',
		    'revisi'=>'0'
		);
		$noUrut=1;
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$dataH[0]['tanggal'],
			'nourut'=>$noUrut,
			'noakun'=>$dtnoakun[0]['noakundebet'],
			'keterangan'=>'Persediaan TBS kode unit :'.$dataH[0]['kodeunit'].' pada tanggal '.$dataH[0]['tanggal'],
			'jumlah'=>$totPersediaan2,
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
			'kodesegment' => '0000000001');
		// $noUrut=2;
		// $dataRes['detail'][] = array(
			// 'nojurnal'=>$nojurnal,
			// 'tanggal'=>$dataH[0]['tanggal'],
			// 'nourut'=>$noUrut,
			// 'noakun'=>$dtnoakun[0]['noakundebet'],
			// 'keterangan'=>'Beban Pajak 22 dari pengakuan penerimaan TBS  pada tanggal '.$dataH[0]['tanggal'],
			// 'jumlah'=>$totPajakDitanggung,
			// 'matauang'=>'IDR',
			// 'kurs'=>'1',
			// 'kodeorg'=>$dataH[0]['kodeunit'],
			// 'kodekegiatan'=>'',
			// 'kodeasset'=>'',
			// 'kodebarang'=>'',
			// 'nik'=>'',
			// 'kodecustomer'=>'',
			// 'kodesupplier'=>'',
			// 'noreferensi'=>$dataH[0]['notransaksi'],
			// 'noaruskas'=>'',
			// 'kodevhc'=>'',
			// 'nodok'=>'',
			// 'kodeblok'=>'',
			// 'revisi'=>'0',
			// 'kodesegment' => '0000000001');
		foreach($lstSupp as $dtSupp){
			foreach($lstKlasifika as $dtKlasifikasi){
				if($rpSupplier[$dtSupp.$dtKlasifikasi]){
					$noUrut++;
					$whr="supplierid='".$dtSupp."'";
					$optSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',$whr);
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$dataH[0]['tanggal'],
						'nourut'=>$noUrut,
						'noakun'=>$dtnoakun[0]['noakunkredit'],
						'keterangan'=>'Penerimaan TBS dari supplier '.$optSupp[$dtSupp].',pada tanggal :    '.$dataH[0]['tanggal'].' No Transaksi :'.$dataH[0]['notransaksi'],
						'jumlah'=>$rpSupplier[$dtSupp.$dtKlasifikasi]*-1,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$dataH[0]['kodeunit'],
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>'',
						'nik'=>'',
						'kodecustomer'=>'',
						'kodesupplier'=>$dtSupp,
						'noreferensi'=>$dataH[0]['notransaksi'],
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>'',
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' => '0000000001'
						);
				}
			}
		}
					// $noUrut++;
					// $dataRes['detail'][] = array(
							// 'nojurnal'=>$nojurnal,
							// 'tanggal'=>$dataH[0]['tanggal'],
							// 'nourut'=>$noUrut,
							// 'noakun'=>'2120200',
							// 'keterangan'=>'Hutang Pajak 22 dari pengakuan penerimaan TBS  pada tanggal '.$dataH[0]['tanggal'],
							// 'jumlah'=>$totPajak*(-1),
							// 'matauang'=>'IDR',
							// 'kurs'=>'1',
							// 'kodeorg'=>$dataH[0]['kodeunit'],
							// 'kodekegiatan'=>'',
							// 'kodeasset'=>'',
							// 'kodebarang'=>'',
							// 'nik'=>'',
							// 'kodecustomer'=>'',
							// 'kodesupplier'=>'',
							// 'noreferensi'=>$dataH[0]['notransaksi'],
							// 'noaruskas'=>'',
							// 'kodevhc'=>'',
							// 'nodok'=>'',
							// 'kodeblok'=>'',
							// 'revisi'=>'0',
							// 'kodesegment' => '0000000001'
							// );
		#=== Insert Data ===
		$errorDB = "";
		# Header
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
		try{$owlPDO->exec($queryH); }catch (PDOException $e) {$errorDB .= "Header :". $e->getMessage() ; }
		# Detail
		if($errorDB=='') {
		    foreach($dataRes['detail'] as $key=>$dataDet) {
		        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
		        try{$owlPDO->exec($queryD); }catch (PDOException $e) {$errorDB .= "Detail: ".$key." ". $e->getMessage() ; }
			}
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
	        		$owlPDO->exec($sdel);
	        		$sInsert="insert into  ".$dbname.".keu_5saldotbsnonramp (`kodeorg`,`kodesupplier`,`tanggal`,`fisik`,`hargasatuan`,`updateby`) 
		        	          values ('".$dataH[0]['kodeunit']."','".$suppId."','".$dataH[0]['tanggal']."','".$saldoBaru."','".$hargaRataBaru."','".$_SESSION['standard']['userid']."')";
		        	try{
		        		$owlPDO->exec($sInsert);
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