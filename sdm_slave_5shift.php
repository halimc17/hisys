<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$str= "select * from ".$dbname.".sdm_5mastershift where status='1' order by id";
$res= fetchdata($str);
foreach($res as $val){
	$arrnamashift[$val['shift']]=$val['namashift'];
}

if($param['keluar_ist']==''){
	$param['keluar_ist']='00:00';
}
if($param['masuk_ist']==''){
	$param['masuk_ist']='00:00';
}

switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$where = " and id='".$param['id']."'";
			$str = "delete from " . $dbname . ".sdm_5shift where 1=1 ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'kodeorg'   => $param['kodeorg'],
					'shift'     => $param['shift'],
					'namashift' => $param['namashift'],
					'masuk'     => $param['masuk'],
					'keluar_ist'=> $param['keluar_ist'],
					'masuk_ist' => $param['masuk_ist'],
					'keluar'    => $param['keluar'],
					'toleransi' => $param['toleransi'],
					'batas_awal' => $param['batasawal'],
					'tipe_shift' => $param['tipe_shift'],
					'updateby'  => $_SESSION['standard']['userid']
				);
				$where = "id='".$param['id']."'";
				$query = updateQuery($dbname,'sdm_5shift',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$str = "select * from ".$dbname.".sdm_5shift where kodeorg='".$param['kodeorg']."' and shift='".$param['shift']."' and namashift='".$param['namashift']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Data sudah pernah diinput.");
			}
			
			$data = array(
				'kodeorg'   => $param['kodeorg'],
				'shift'     => $param['shift'],
				'namashift' => $param['namashift'],
				'masuk'     => $param['masuk'],
				'keluar_ist'=> $param['keluar_ist'],
				'masuk_ist' => $param['masuk_ist'],
				'keluar'    => $param['keluar'],
				'toleransi' => $param['toleransi'],
				'batas_awal' => $param['batasawal'],
				'tipe_shift' => $param['tipe_shift'],
				'createby'  => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'sdm_5shift',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'getnamashift':
		$optshiftnama="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str= "select * from ".$dbname.".sdm_5mastershift where status='1' order by id";
		$res= fetchdata($str);
		foreach($res as $val){
			$sel="";
			if($param['namashift']==$val['shift']){
				$sel="selected";
			}
			if($param['id']>'1' and ($val['shift']=='KBN' or $val['shift']=='KTR' or $val['shift']=='SPV')){
				$optshiftnama.="<option value=".$val['shift']." disabled ".$sel.">".$val['shift']." - ".$val['namashift']."</option>";
			}else{				
				$optshiftnama.="<option value=".$val['shift']." ".$sel.">".$val['shift']." - ".$val['namashift']."</option>";
			}
		}
		
		
		echo $optshiftnama;
	break;
	case 'addnew':
		$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach(getOrgDetail(11) as $key => $val){
			$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
			$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
			$d=$induk[$key];
			if($d!=$n){			
				$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			if($param['kodeorg']==$key){
				$optorg.="<option value=".$key." selected>".$key." - ".$val."</option>";
			}else{				
				$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
			}
			$n=$d;
			if($d!=$n){			
				$optorg.="</optgroup>";
			}
		}
		
		$arrshift=array('1'=>'(1) Pertama','2'=>'(2) Kedua','3'=>'(3) Ketiga');
		$optshift="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arrshift as $val => $key){
			if($param['shift']==$val){
				$optshift.="<option value=".$val." selected>".$key."</option>";
			}else{				
				$optshift.="<option value=".$val.">".$key."</option>";
			}
		}
		
		
		$optshiftnama="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str= "select * from ".$dbname.".sdm_5mastershift where status='1' order by id";
		$res= fetchdata($str);
		foreach($res as $val){
			if($param['namashift']==$val['shift']){
				$optshiftnama.="<option value=".$val['shift']." selected>".$val['shift']." - ".$val['namashift']."</option>";
			}else{				
				$optshiftnama.="<option value=".$val['shift'].">".$val['shift']." - ".$val['namashift']."</option>";
			}
		}
		

		$arrtipeshift=array('1'=>'(1) Jam Masuk Dan Keluar','2'=>'(2) Salah Satu');
		$opttipeshift="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arrtipeshift as $val => $key){
			if($param['shift']==$val){
				$opttipeshift.="<option value=".$val." selected>".$key."</option>";
			}else{				
				$opttipeshift.="<option value=".$val.">".$key."</option>";
			}
		}
		if($param['mode']=='update'){
			$tombol='Update';
		}else{
			$tombol='Save';
		}
		
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<input id=id type=hidden>
					<td style=min-width:100px>".$_SESSION['lang']['kodeorg']."</td>
					<td colspan=3><select class='select2' style='width:542px;' id=kodeorg >".$optorg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['shift']."</td>
					<td><select class='select2' style='width:200px;' onchange=getnamashift(this.value) id=shift >".$optshift."</select></td>
					
					<td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['shift']."</td>
					<td><select class='select2' style='width:200px;' id=namashift >".$optshiftnama."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jammasuk']."</td>
					<td><input type=time class=myinputtextnumber style='width:195px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=masuk ></td>
					
					<td>".$_SESSION['lang']['jamkeluar']."</td>
					<td><input type=time class=myinputtextnumber style='width:195px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=keluar ></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jamistirahatdari']."</td>
					<td><input type=time class=myinputtextnumber style='width:195px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=istirahatmulai ></td>
					
					<td>".$_SESSION['lang']['jamistirahatsampai']."</td>
					<td><input type=time class=myinputtextnumber style='width:195px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=istirahatselesai ></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['batas']." Awal</td>
					<td><input type=time class=myinputtextnumber style='width:195px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=batasawal ></td>

					<td>".$_SESSION['lang']['tipe']." </td>
					<td><select class='select2' style='width:200px;' id=tipe_shift >".$opttipeshift."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['toleransi']." <b> (Menit) </b></td>
					<td><input class=myinputtextnumber style='width:195px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" onkeypress=\"return angka_doang(event);\" id=toleransi ></td>					
				</tr>
                <tr>
                    <td colspan=40 align=center>
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:160px;height:30px' class=mybutton>".$tombol."</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodeorg']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['shift']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['shift']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jammasuk']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jamistirahatdari']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jamistirahatsampai']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['jamkeluar']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['toleransi']." ".$_SESSION['lang']['jammasuk']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['batas']." Awal</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tipe']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['updateby']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tanggal']."</th>
				<th style='text-align:center;' colspan=2>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";
		
		$arrtipeshift=array('1'=>'Jam Masuk Dan Keluar','2'=>'Salah Satu');
		

		$str= "select * from ".$dbname.".sdm_5shift where kodeorg in (".getOrgDetail(2).") order by kodeorg desc, namashift asc";
		$res= fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$bar['kodeorg']." - ".getNamaOrg($bar['kodeorg'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['shift']."</td>";
			$tab.="<td style='text-align:left;'>".$arrnamashift[$bar['namashift']]."</td>";
			$tab.="<td style='text-align:center;'>".$bar['masuk']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['keluar_ist']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['masuk_ist']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['keluar']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['toleransi']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['batas_awal']."</td>";
			$tab.="<td style='text-align:center;'>".$arrtipeshift[$bar['tipe_shift']]."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($bar['updateby'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['updatetime']."</td>";
			
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['kodeorg']."','".$bar['shift']."','".$bar['namashift']."','".$bar['masuk']."','".$bar['keluar_ist']."','".$bar['masuk_ist']."','".$bar['keluar']."','".$bar['toleransi']."','".$bar['id']."','".$bar['batas_awal']."','".$bar['tipe_shift']."')\";></td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['id']."');></td>";
			$tab.="</tr>";

			$n=$d;
			$o=$e;
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
}
?>
