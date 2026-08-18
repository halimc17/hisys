<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$kode = checkPostGet('kode','');
$pekerjaan = checkPostGet('pekerjaan','');
$volume = checkPostGet('volume','');
$satuan = checkPostGet('satuan','');
$lokasi = checkPostGet('lokasi','');
$status = checkPostGet('status','');

$kodedethid = checkPostGet('kodedethid','');
$kodekeghid = checkPostGet('kodekeghid','');

$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");

$kodedet = checkPostGet('kodedet','');
$pekerjaandet = checkPostGet('pekerjaandet','');
$nourutdet = checkPostGet('nourutdet','');
$statusdet = checkPostGet('statusdet','');

$txtcari = checkPostGet('txtcari','');

$kodekeg = checkPostGet('kodekeg','');
$kegiatan = checkPostGet('kegiatan','');
$volumekeg = checkPostGet('volumekeg','');
$satuankeg = checkPostGet('satuankeg','');
$nourutkeg = checkPostGet('nourutkeg','');
$statuskeg = checkPostGet('statuskeg','');

$kodemat = checkPostGet('kodemat','');
$namamat = checkPostGet('namamat','');

switch ($method) 
{
	case 'insert':
		$str="select * from ".$dbname.".vhc_5rab where pekerjaan='".$pekerjaan."'";
		$res=fetchData($str);
		
		if(count($res) > 0)
		{
			exit("Gagal : Pekerjaan ini sudah ada di list data.");
		}
		
		$str = "insert into ".$dbname.".vhc_5rab values ('','".$pekerjaan."','".$volume."','".$satuan."','".$lokasi."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

    case 'update':
		$str = "update ".$dbname.".vhc_5rab set status='".$status."', updateby='".$_SESSION['standard']['userid']."' where kode = '".$kode."'";
		
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;

    case'loaddata':
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['pekerjaan']."</td>
				<td align=center>".$_SESSION['lang']['volume']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center style='display:none'>".$_SESSION['lang']['lokasi']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center colspan=3>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".vhc_5rab order by pekerjaan asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$kodetipe=substr($bar['pekerjaan'],0,2);
			$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$optSubAsset = makeOption($dbname,"sdm_5subtipeasset",'kodesub,namasub',"kodesub='".substr($bar['pekerjaan'],2,2)."' and kodetipe='".$kodetipe."'");
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['kode']."</td>";
            $tab.="<td>".$optSubAsset[substr($bar['pekerjaan'],2,2)]."</td>";
            $tab.="<td style='text-align:right'>".$bar['volume']."</td>";
            $tab.="<td>".$bar['satuan']."</td>";
            $tab.="<td style='display:none'>".$bar['lokasi']."</td>";
            $tab.="<td align=center>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td align=left>".$optNamaKar[$bar['updateby']]."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['kode']."','".$bar['pekerjaan']."','".$bar['volume']."','".$bar['satuan']."','".$bar['lokasi']."','".$bar['status']."');\">
			</td>";
			$tab.="<td align=center>
				<img src=images/plus.png class=resicon  title='Add Detail ' onclick=\"addDetail('".$bar['kode']."','".$optSubAsset[substr($bar['pekerjaan'],2,2)]."',event);\">
			</td>";
			$tab.="<td align=center>
				<img onclick=\"previewDetail('".$bar['kode']."','".$optSubAsset[substr($bar['pekerjaan'],2,2)]."',event);\" title=\"Detail RAB\" class=\"resicon\" src=\"images/zoom.png\">
			</td>";

            $tab.="</tr>";
        }
		
		echo $tab;
	break;
	
	case'adddetail':
		$tab="";
		
		$tab.="<div id='divdet'><fieldset>
			<legend>".$_SESSION['lang']['form']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>".$_SESSION['lang']['kode']."</td> 
					<td>:</td>
					<td>
						<input type=text id=kodedet class=myinputtext style='width:115px;' maxlength=100 disabled>
						<input type=hidden id=kodedethid value='".$kode."'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['detail']." ".$_SESSION['lang']['pekerjaan']."</td> 
					<td>:</td>
					<td><input type=text onkeydown=\"upperCaseF(this)\" id=pekerjaandet onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" maxlength=100></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['urutan']."</td> 
					<td>:</td>
					<td>
						<input type=text id=nourutdet class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:50px;' maxlength=50 value=''>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']." Aktif / Non-Aktif</td> 
					<td>:</td>
					<td>
						<input type=checkbox id=statusdet checked>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td colspan=3>
						<button class=mybutton onclick=simpandet()>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=bataldet()>".$_SESSION['lang']['cancel']."</button>
						<input type=hidden id=methoddet value='insertdet'>
					</td>
				</tr>
			</table>
			
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['keterangan']."</legend>
							".$_SESSION['lang']['kode']." : Auto Generate<br>
							Status :<br>
							&nbsp;- Aktif : Centang CheckBox <input type='checkbox' checked disabled><br>
							&nbsp;- Non Aktif : Uncentang CheckBox <input type='checkbox' disabled>
						</fieldset>
					</td> 
				</tr>
			</table>
		</fieldset>
		<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<div id=containerdet> 
				<script>loaddatadet(0)</script>
			</div>
		</fieldset>
		</div>
		<div id='divkeg'>
		</div>";
		
		echo $tab;
	break;
	
	case 'insertdet':
		$str = "insert into ".$dbname.".vhc_5rabdet(koderab,dekripsi,nourut,status,createdby,createdtime,updateby,updatetime) values ('".$kodedethid."','".$pekerjaandet."','".$nourutdet."','".$statusdet."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'updatedet':
		$str = "update ".$dbname.".vhc_5rabdet set status='".$statusdet."',nourut='".$nourutdet."', updateby='".$_SESSION['standard']['userid']."' where kode = '".$kodedet."'";
		
        try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'loaddatadet':
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['detail']." ".$_SESSION['lang']['pekerjaan']."</td>
				<td align=center>".$_SESSION['lang']['urutan']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center colspan=2>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".vhc_5rabdet where koderab='".$kodedethid."' order by nourut asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['kode']."</td>";
            $tab.="<td>".$bar['dekripsi']."</td>";
            $tab.="<td style='text-align:center'>".$bar['nourut']."</td>";
            $tab.="<td align=center>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td align=left>".$optNamaKar[$bar['updateby']]."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit Description' onclick=\"editdet('".$bar['kode']."','".$bar['dekripsi']."','".$bar['nourut']."','".$bar['status']."');\">
			</td>";
			$tab.="<td align=center>
				<img src=images/plus.png class=resicon  title='Add Kegiatan ' onclick=\"addkeg('".$bar['kode']."','".$bar['dekripsi']."',event);\">
			</td>";

            $tab.="</tr>";
        }
		 $tab.="</table>";
		
		echo $tab;
	break;
	
	case'getdetail':
		$tab="";
		
		$str="select * from ".$dbname.".vhc_5rab where kode='".$kode."'";
		$res=fetchData($str);
		
		$optSubAsset = makeOption($dbname,"sdm_5subtipeasset",'kodesub,namasub',"kodesub='".$res[0]['pekerjaan']."' and kodetipe='BG'");
		
		$kode=$res[0]['kode'];
		$pekerjaan=$optSubAsset[$res[0]['pekerjaan']];
		$volume=$res[0]['volume'];
		$satuan=$res[0]['satuan'];
		$lokasi=$res[0]['lokasi'];
		
		$tab="<table cellpadding=1 cellspacing=0>
			<tr>
				<td colspan=9 style='text-align:center;font-weight:bold'>BILL OF QUANTITY</td>
			</tr>
			<tr>
				<td colspan=9 style='text-align:center;font-weight:bold'>BQ</td>
			</tr>
			<tr>
				<td></td>
				<td>".$_SESSION['lang']['pekerjaan']."</td>
				<td>:</td>
				<td colspan='6'>".$pekerjaan."</td>
			</tr>
			<tr>
				<td></td>
				<td>".$_SESSION['lang']['volume']."</td>
				<td>:</td>
				<td colspan='6'>".$volume." ".$satuan."</td>
			</tr>
			<tr style='display:none'>
				<td></td>
				<td>".$_SESSION['lang']['lokasi']."</td>
				<td>:</td>
				<td colspan='6'>".$lokasi."</td>
			</tr>
			
			<tr style='font-weight:bold;text-align:center'>
				<td style='border:1px solid grey'>NO</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey' colspan=4>URAIAN PEKERJAAN</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>VOLUME</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>SATUAN</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>HARGA SATUAN (Rp)</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>JUMLAH HARGA (Rp)</td>
			</tr>";
		
		$str="select * from ".$dbname.".vhc_5rabdet where koderab='".$kode."' order by nourut asc";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val)
		{
			$no++;
			
			$tab.="<tr style='font-weight:bold'>
				<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'>".romawi($no)."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey' colspan=4>".$val['dekripsi']."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
			</tr>";
			
			$str2="select * from ".$dbname.".vhc_5rabkeg where kodedet='".$val['kode']."' order by nourut asc";
			$res2=fetchData($str2);
			$nodet=0;
			foreach($res2 as $key2=>$val2)
			{
				$nodet++;
				
				$tab.="<tr>
					<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'>".$nodet."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey' colspan=4>".$val2['kegiatan']."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:right'>".number_format($val2['volume'],2)."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey'>".$val2['satuan']."</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				</tr>";
			}
			
			$tab.="<tr>
					<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'></td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey' colspan=4>&nbsp;</td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
					<td style='border-right:1px solid grey;border-bottom:1px solid grey'></td>
				</tr>";
		}
			
		$tab.="</table>";
		
		echo $tab;
	break;
	
	
	
	case'addkeg':
		$optSatuan = "";
		$nmSatuan=makeOption($dbname,'setup_satuan','satuan,satuan');
		foreach($nmSatuan as $val)
		{
			$optSatuan.="<option value='".$val."'>".$val."</option>";
		}
		$tab.="<div style='text-align:right;'><img src=images/refresh.png class=resicon caption='Edit Description' onclick=\"back();\">&nbsp;<label style='color:blue;cursor:pointer;' onclick=\"back();\">BACK</label></div>
		<fieldset>
			<legend>".$pekerjaandet."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>".$_SESSION['lang']['kode']."</td> 
					<td>:</td>
					<td>
						<input type=text id=kodekeg class=myinputtext style='width:115px;' maxlength=100 disabled>
						<input type=hidden id=kodekeghid value='".$kodedet."'>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kegiatan']."</td> 
					<td>:</td>
					<td><input type=text id=kegiatan onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" maxlength=100></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['volume']."</td> 
					<td>:</td>
					<td>
						<input type=text id=volumekeg class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:115px;' maxlength=100 value='0'>
						<select id='satuankeg' style='width:80px;'>".$optSatuan."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['urutan']."</td> 
					<td>:</td>
					<td>
						<input type=text id=nourutkeg class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style='width:50px;' maxlength=50 value=''>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']." Aktif / Non-Aktif</td> 
					<td>:</td>
					<td>
						<input type=checkbox id=statuskeg checked>
					</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td colspan=3>
						<button class=mybutton onclick=simpankeg()>".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=batalkeg()>".$_SESSION['lang']['cancel']."</button>
						<input type=hidden id=methodkeg value='insertkeg'>
					</td>
				</tr>
			</table>
			
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['material']."</legend>
							<table class=sortable cellpadding=1 cellspacing=1 border=0>
								<thead>
								<tr class=rowheader>
									<td align=center>".$_SESSION['lang']['kode']."</td>
									<td align=center>".$_SESSION['lang']['nama']."</td>
									<td align=center>".$_SESSION['lang']['action']."</td>
								</tr>
								</thead>
								<tbody id='listmaterial'>
								</tbody>
								<tr class='rowcontent'>
									<td align=center>
										<input type=text id=kodemat class=myinputtext onkeypress=\"return angka_doang(event);\"  style='width:60px;' style='text-align:center' readonly>
									</td>
									<td align=center>
										<input type=text id=namamat class=myinputtext onkeypress=\"return angka_doang(event);\" style='width:200px;' readonly>
										<img src=images/zoom.png class=resicon  caption='Search Material' onclick=\"searchmat('Cari Nama Barang',event);\">
									</td>
									<td align=center>
										<img src=images/plus.png class=resicon  title='Add Materoal' onclick=\"addmat();\">
									</td>
								</tr>
								<tbody>
								</tbody>
							</table>
						</fieldset>
					</td> 
				</tr>
			</table>
		</fieldset>
		<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<div id=containerkeg></div>
		</fieldset>";
		
		echo $tab;
	break;
	
	case'loaddatakeg':
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['kegiatan']."</td>
				<td align=center>".$_SESSION['lang']['volume']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center>".$_SESSION['lang']['urutan']."</td>
				<td align=center>".$_SESSION['lang']['material']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['updateby']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".vhc_5rabkeg where kodedet='".$kodekeghid."' order by nourut asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$optNamaKar = makeOption($dbname,"datakaryawan",'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
			$no++;
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center style='vertical-align:top'>".$no."</td>";
            $tab.="<td style='vertical-align:top'>".$bar['kode']."</td>";
            $tab.="<td style='vertical-align:top'>".$bar['kegiatan']."</td>";
            $tab.="<td style='text-align:right;vertical-align:top'>".$bar['volume']."</td>";
            $tab.="<td style='vertical-align:top'>".$bar['satuan']."</td>";
            $tab.="<td style='text-align:center;vertical-align:top'>".$bar['nourut']."</td>";
            $tab.="<td>
				<table>";
			
			$str2="select * from ".$dbname.".vhc_5rabmat where kodekeg='".$bar['kode']."'";
			$res2=fetchData($str2);
			$no2=0;
			foreach($res2 as $key=>$val)
			{
				$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['material']."'");
				$no2++;
				$tab.="<tr>
					<td>".$no2."</td>
					<td>".$val['material']."</td>
					<td>".$optNamaBarang[$val['material']]."</td>
				</tr>";
			}
			$tab.="</table>
			</td>";
            $tab.="<td align=center style='vertical-align:top'>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td align=left style='vertical-align:top'>".$optNamaKar[$bar['updateby']]."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit Description' onclick=\"editkeg('".$bar['kode']."','".$bar['kegiatan']."','".$bar['volume']."','".$bar['satuan']."','".$bar['nourut']."','".$bar['status']."');\">
			</td>";

            $tab.="</tr>";
        }
		 $tab.="</table>";
		
		echo $tab;
	break;
	
	case 'insertkeg':
		$str="select max(kode) as jlh from ".$dbname.".vhc_5rabkeg";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
		$mykode = ($bar['jlh']+1);
		
		$str = "insert into ".$dbname.".vhc_5rabkeg(kode,kodedet,kegiatan,volume,satuan,nourut,status,createdby,createdtime,updateby,updatetime) values ('".$mykode."','".$kodekeghid."','".$kegiatan."','".$volumekeg."','".$satuankeg."','".$nourutkeg."','".$statuskeg."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
		try
		{
			$owlPDO->exec($str);
			
			$str = "delete from ".$dbname.".vhc_5rabmat where kodekeg='".$mykode."'";
			try
			{
				$owlPDO->exec($str);
				
				if($_SESSION['rabmaterial'] != array())
				{
					foreach($_SESSION['rabmaterial'] as $key=>$row)
					{
						$str = "insert into ".$dbname.".vhc_5rabmat(kodekeg,material,status,createdby,createdtime,updateby,updatetime) values ('".$mykode."','".$row['kodemat']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
						try
						{
							$owlPDO->exec($str);
						}
						catch(PDOException $e)
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
					}
				}
				$_SESSION['rabmaterial'] = array();
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'updatekeg':
		$str = "update ".$dbname.".vhc_5rabkeg set status='".$statuskeg."',nourut='".$nourutkeg."', updateby='".$_SESSION['standard']['userid']."' where kode = '".$kodekeg."'";
		
        try
		{
			$owlPDO->exec($str);
			
			$str = "delete from ".$dbname.".vhc_5rabmat where kodekeg='".$kodekeg."'";
			try
			{
				$owlPDO->exec($str);
				
				if($_SESSION['rabmaterial'] != array())
				{
					foreach($_SESSION['rabmaterial'] as $key=>$row)
					{
						$str = "insert into ".$dbname.".vhc_5rabmat(kodekeg,material,status,createdby,createdtime,updateby,updatetime) values ('".$kodekeg."','".$row['kodemat']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','')";
						try
						{
							$owlPDO->exec($str);
						}
						catch(PDOException $e)
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
					}
				}
				$_SESSION['rabmaterial'] = array();
			}
			catch(PDOException $e)
			{
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'caribarang':
		$tab="";
		$no=0;
		
		$tab.="<table class=sortable cellspacing=1 border=0 style=width:100%>
			<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
			</tr>
			</thead>
			<tbody>";
			
		$str="select a.kodebarang,a.namabarang,a.satuan from ".$dbname.".log_5masterbarang a where (a.namabarang like '%".$txtcari."%' or kodebarang like '%".$txtcari."%')";
		$res=fetchData($str);
		foreach($res as $val)
		{
			$no+=1;
			$tab.="<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"loadField('".$val['kodebarang']."','".$val['namabarang']."');\">
				<td align=center>".$no."</td>
				<td align=center>".$val['kodebarang']."</td>
				<td>".$val['namabarang']."</td>
				<td>".$val['satuan']."</td>
			</tr>";	
		}
		$tab.="</table>";
		
		echo $tab;
	break;
	
	case'addmat':
		$newdata = array(
			'kodemat'=>$kodemat,
			'namamat'=>$namamat
		);
		
		if($_SESSION['rabmaterial'] != array())
		{
			foreach($_SESSION['rabmaterial'] as $key=>$row)
			{
				if($row['kodemat'] == $kodemat)
				{
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['rabmaterial'],$newdata);
		}else{
			array_push($_SESSION['rabmaterial'],$newdata);
		}
	break;
	
	case'loaddatamat':
		$tab="";
		foreach($_SESSION['rabmaterial'] as $key=>$row)
		{
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center'>".$row['kodemat']."</td>";
			$tab.="<td>".$row['namamat']."</td>";
			$tab.="<td style='text-align:center'>
				<img title='Delete' class=resicon onclick=\"deletemat('".$row['kodemat']."')\" src='images/delete_32.png'/
			</td>";
			$tab.="</tr>";
		}
		
		echo $tab;
	break;
	
	case'deletemat':
		foreach($_SESSION['rabmaterial'] as $key=>$row)
		{
			if($row['kodemat'] == $kodemat)
			{
				unset($_SESSION['rabmaterial'][$key]);
			}
		}
	break;
	
	case'editkeg':
		$_SESSION['rabmaterial'] = array();
		$str="select * from ".$dbname.".vhc_5rabmat where kodekeg='".$kodekeg."'";
		$res=fetchData($str);
		$no=0;
		foreach($res as $val)
		{
			$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['material']."'");
			$_SESSION['rabmaterial'][$no]['kodemat'] = $val['material'];			
			$_SESSION['rabmaterial'][$no]['namamat'] = $optNamaBarang[$val['material']];	
			$no++;
		}
	break;
	
	case'batalkeg':
		$_SESSION['rabmaterial'] = array();
	break;

    default:
	break;
}
?>
