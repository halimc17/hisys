<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$id = checkPostGet('id','');
$kriteria = checkPostGet('kriteria','');
$status = checkPostGet('status','');

$iddt = checkPostGet('iddt','');
$subkriteria = checkPostGet('subkriteria','');
$vertagihan = checkPostGet('vertagihan','');
$verkasbank = checkPostGet('verkasbank','');
$statusdet = checkPostGet('statusdet','');

$arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif"); 
$path = "fileupload/filingsystem/";

switch ($method) 
{
	case 'insert':
		try{	
			$createdby=$_SESSION['standard']['userid'];
			$createdtime=date('Y-m-d H:i:s');
			$updateby=$_SESSION['standard']['userid'];
			$updatetime=date('Y-m-d H:i:s');
			$structure = $path."".$kriteria;
		
			$str="select * from ".$dbname.".fil_5mapht where lower(foldername)='".strtolower($kriteria)."'";
			$res=fetchData($str);
			
			if(count($res) > 0){
				throw new PDOException("Kriteria ini sudah ada di list data.");
			}
			
			if (!mkdir($structure, 0777, true)){
				throw new PDOException("Failed to create folders...");
			}
			
			$str = "insert into ".$dbname.".fil_5mapht values ('','".$kriteria."','".$status."')";
		
			$owlPDO->beginTransaction();
			$owlPDO->exec($str);
			$myid = $owlPDO->lastInsertId();
			
			$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('1','0','".$kriteria."','folder','','1','".$createdby."','".$createdtime."','".$updateby."','".$updatetime."','".$myid."')";
			$owlPDO->exec($str);
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
		}
	break;

    case 'update':
		try{
			$owlPDO->beginTransaction();
			$str="select * from ".$dbname.".fil_5mapht where lower(foldername)='".strtolower($kriteria)."' and id!='".$id."'";
			$res=fetchData($str);
			
			if(count($res) > 0){
				throw new PDOException("Kriteria ini sudah ada di list data.");
			}
			
			$myid = getidfilemanager($id);
			$pathold = setlocationfile($myid);
			$exppathold = explode('/',$pathold);
			$temppathnew = "";
			$no=0;
			foreach($exppathold as $key)
			{
				$no++;
				if($no!=count($exppathold))
				{
					$temppathnew .= $key."/";
				}
			}
			$pathnew = $temppathnew."".$kriteria;
			$valname = $kriteria;
			
			$str="update ".$dbname.".filemanager set namafile='".$valname."', updateby='".$updateby."', updatetime='".$updatetime."' where id='".$myid."'";
			$owlPDO->exec($str);
			rename($pathold,$pathnew);
			
			$str = "update ".$dbname.".fil_5mapht set foldername='".$kriteria."', status='".$status."' where id='".$id."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
		}
	break;

    case'loaddata':
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['id']."</td>
				<td align=center>".$_SESSION['lang']['kriteria']."</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center colspan=3>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$no = 0;
		$str = "select * from ".$dbname.".fil_5mapht order by foldername asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$bar['id']."</td>";
            $tab.="<td>".$bar['foldername']."</td>";
            $tab.="<td align=center>".$arrstatus[$bar['status']]."</td>";
			$tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['id']."','".$bar['foldername']."','".$bar['status']."');\">
			</td>";
			$tab.="<td align=center>
				<img src=images/plus.png class=resicon  title='Add Detail ' onclick=\"addDetail('".$bar['id']."','".$bar['foldername']."',event);\">
			</td>";
			$tab.="<td align=center>
				<img onclick=\"previewDetail('".$bar['id']."','".$bar['foldername']."',event);\" title=\"Detail Maping E-Filing\" class=\"resicon\" src=\"images/zoom.png\">
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
					<td>".$_SESSION['lang']['id']."</td> 
					<td>:</td>
					<td>
						<input type=text id=iddt class=myinputtext style='width:115px;' maxlength=100 disabled>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['detail']."</td> 
					<td>:</td>
					<td><input type=text id=subkriteria onkeypress=\"return tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" maxlength=100></td>
				</tr>
				<tr>
					<td>Verifikasi Tagihan</td> 
					<td>:</td>
					<td>
						<input type=checkbox id=vertagihan>
					</td>
				</tr>
				<tr>
					<td>Verifikasi Kas Bank</td> 
					<td>:</td>
					<td>
						<input type=checkbox id=verkasbank>
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
		try{
			$owlPDO->beginTransaction();
			
			$createdby=$_SESSION['standard']['userid'];
			$createdtime=date('Y-m-d H:i:s');
			$updateby=$_SESSION['standard']['userid'];
			$updatetime=date('Y-m-d H:i:s');
			
			$str="select * from ".$dbname.".fil_5mapdt where lower(foldername)='".strtolower($kriteria)."' and idht='".$id."'";
			$res=fetchData($str);
			
			if(count($res) > 0){
				throw new PDOException('Kriteria ini sudah ada di list data.');
			}
			
			$str="select * from ".$dbname.".fil_5mapht where id='".$id."'";
			$res=fetchData($str);
			$kriteria=$res[0]['foldername'];
			$structure = $path."".$kriteria.'/'.$subkriteria;
			
			if (!mkdir($structure, 0777, true)){
				throw new PDOException('Failed to create folders...');
			}
			
			$str = "insert into ".$dbname.".fil_5mapdt(id,idht,foldername,vertagihan,verkasbank,status) values ('','".$id."','".$subkriteria."','".$vertagihan."','".$verkasbank."','".$statusdet."')";
			$owlPDO->exec($str);
			$myid = $owlPDO->lastInsertId();
			
			// $str="select * from ".$dbname.".filemanager where sourceid='".$id."'";
			// $res=fetchData($str);
			// $induk = $res[0]['id'];
			
			// $str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$induk."','1','".$subkriteria."','folder','','1','".$createdby."','".$createdtime."','".$updateby."','".$updatetime."','".$myid."')";
			// $owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
		}
	break;
	
	case 'updatedet':
		try{
			$owlPDO->beginTransaction();
			
			$str="select * from ".$dbname.".fil_5mapdt where lower(foldername)='".strtolower($subkriteria)."' and idht='".$id."' and id!='".$iddt."'";
			$res=fetchData($str);
			
			if(count($res) > 0){
				throw new PDOException("Kriteria ini sudah ada di list data.");
			}
			
			$myid = getidfilemanager($iddt,'1');
			$pathold = setlocationfile($myid);
			$exppathold = explode('/',$pathold);
			$temppathnew = "";
			$no=0;
			foreach($exppathold as $key)
			{
				$no++;
				if($no!=count($exppathold))
				{
					$temppathnew .= $key."/";
				}
			}
			$pathnew = $temppathnew."".$subkriteria;
			$valname = $subkriteria;
			
			// $str="update ".$dbname.".filemanager set namafile='".$valname."', updateby='".$updateby."', updatetime='".$updatetime."' where id='".$myid."'";
			// $owlPDO->exec($str);
			// rename($pathold,$pathnew);
			
			$str = "update ".$dbname.".fil_5mapdt set foldername='".$subkriteria."', vertagihan='".$vertagihan."', verkasbank='".$verkasbank."', status='".$statusdet."' where id = '".$iddt."'";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
		}
	break;
	
	case'loaddatadet':
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['id']."</td>
				<td align=center>".$_SESSION['lang']['detail']."</td>
				<td align=center>Verifikasi Tagihan</td>
				<td align=center>Verifikasi Kas Bank</td>
				<td align=center>".$_SESSION['lang']['status']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
		
		$str = "select * from ".$dbname.".fil_5mapdt where idht='".$id."' order by foldername asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch())
		{
			$tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$bar['id']."</td>";
            $tab.="<td>".$bar['foldername']."</td>";
            $tab.="<td align=center>".($bar['vertagihan']=='1'?"&#10004;":"")."</td>";
            $tab.="<td align=center>".($bar['verkasbank']=='1'?"&#10004;":"")."</td>";
            $tab.="<td align=center>".$arrstatus[$bar['status']]."</td>";
            $tab.="<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit Description' onclick=\"editdet('".$bar['id']."','".$bar['foldername']."','".$bar['vertagihan']."','".$bar['verkasbank']."','".$bar['status']."');\">
			</td>";

            $tab.="</tr>";
        }
		 $tab.="</table>";
		
		echo $tab;
	break;
	
	case'getdetail':
		$tab="";
		
		$str="select * from ".$dbname.".fil_5mapht where id='".$id."'";
		$res=fetchData($str);
		
		$idht=$res[0]['id'];
		$foldername=$res[0]['foldername'];
		
		$tab="<table cellpadding=1 cellspacing=0>
			<tr>
				<td colspan=4 style='text-align:center;font-weight:bold'>E-Filing Maping</td>
			</tr>
			<tr>
				<td></td>
				<td style='height:40px' colspan=3>".$_SESSION['lang']['kriteria']." : ".$foldername."</td>
			</tr>
			<tr style='font-weight:bold;text-align:center'>
				<td style='border:1px solid grey'>".$_SESSION['lang']['id']."</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>".$_SESSION['lang']['detail']."</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>Verifikasi Tagihan</td>
				<td style='border-right:1px solid grey;border-top:1px solid grey;border-bottom:1px solid grey'>Verifikasi Kas Bank</td>
			</tr>";
		
		$str="select * from ".$dbname.".fil_5mapdt where idht='".$idht."' order by foldername asc";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$tab.="<tr>
				<td style='border-left:1px solid grey;border-bottom:1px solid grey;border-right:1px solid grey;text-align:center'>".$val['id']."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey'>".$val['foldername']."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:center'>".($val['vertagihan']=='1'?"&#10004;":"")."</td>
				<td style='border-right:1px solid grey;border-bottom:1px solid grey;text-align:center'>".($val['verkasbank']=='1'?"&#10004;":"")."</td>
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

function setlocationfile($id)
{
	global $dbname;
	global $owlPDO;
	global $path;
	
	$val = "";
	$tempval = "";
	$curid = $id;
	
	$level = makeOption($dbname,'filemanager','id,level',"id='".$id."'");
	for($i=0;$i<=$level[$id];$i++)
	{
		$str="select * from ".$dbname.".filemanager where id='".$curid."' and status='1'";
		$res=fetchData($str);
		if($i==0)
		{
			$val=$res[0]['namafile'];
		}
		else
		{
			$val=$res[0]['namafile']."/".$tempval;
		}
		$tempval=$val;
		$curid = $res[0]['induk'];
	}
	
	return $path."".$val;
}

function getidfilemanager($id,$level='0')
{
	global $dbname;
	global $owlPDO;
	
	$str="select * from ".$dbname.".filemanager where sourceid='".$id."' and level='".$level."'";
	$res=fetchData($str);
	$val=$res[0]['id'];
		
	return $val;
}
?>
