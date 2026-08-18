<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$arrstatus=array('1'=>'YA','0'=>'TIDAK');
$arrtglbulan=array('1'=>'(MM-YY)','0'=>'(YY-MM)');
$arrtipekar=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe','aktif=1');
$arrtipekar2=makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe','aktif=1','','',true);

switch($method){
	case 'loaddata':

		$tab .= "
        <style>
			td .badge {
				background-color: rebeccapurple;
				color: #fff;
				font-weight: bold;
				font-size: 80%;
				border-radius: 10em;
				min-width: 1.5em;
				padding: 0.25em;
				text-align: center;
			}
		</style>";

		
		
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nourut']."</th>
				<th align=center>".$_SESSION['lang']['kodept']."</th>
				<th align=center>".$_SESSION['lang']['tipekaryawan']."</th>
				<th align=center>Jumlah Counter</th>
				<th align=center>Counter</th>
				<th align=center>TMK (Tanggal Masuk Kerja)</th>
				<th align=center>Format Tanggal NIK</th>
				<th align=center>".$_SESSION['lang']['updatetime']."</th>
				<th align=center>".$_SESSION['lang']['updateby']."</th>
				<th align=center>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody>";

		$str="select * from ".$dbname.".sdm_5tipekaryawan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$nmtipe[$bar['id']]=$bar['tipe'];
		}	
		
		$no=0;
		$flag = 0;
		$whrpt= getOrgDetail(4);
		$where = " where kodept in (".$whrpt.")";
		
		$str= "select * from ".$dbname.".sdm_5formatnik ".$where;
		$res= fetchdata($str);
		foreach($res as $val){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".getNamaOrg($val['kodept'])."</td>";

			$dtpkar=explode(",",$val['tipekaryawan']);
				$tab.="<td>";
					foreach($dtpkar as $tp){
						$tab.="<span class='badge'>". $tp." </span> - ".$nmtipe[$tp]."<br>";
					}
				$tab.="</td>";
				
			$tab.="<td style='text-align:center;'>".$val['jumlahcounter']."</td>";
			$tab.="<td style='text-align:center;'>".$val['counter']."</td>";
			$tab.="<td style='text-align:center;'>".$arrstatus[$val['tmk']]."</td>";
			$tab.="<td style='text-align:center;'>".$arrtglbulan[$val['tglbulan']]."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormald($val['updatetime'])."</td>";
			$tab.="<td style='text-align:center;'>".getNamaKaryawan($val['updateby'])."</td>";

			## Ceka apakah ada nik nya sudah ada belum
			$str="select count(*) as jlhitem  from ".$dbname.".datakaryawan where kodeorganisasi = '".$val['kodept']."' and tipekaryawan in (".$val['tipekaryawan'].") and nik != '' ";
			$res= fetchdata($str);
			$cek_data = $res[0]['jlhitem'];
				
			if($cek_data > 0){
				$flag = 1;
			}else{
				$flag = 0;
			}

			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$val['id']."','".$val['kodept']."','".$val['tipekaryawan']."','".$val['jumlahcounter']."','".$val['counter']."','".$flag."')\";>
			</td>";
			$tab.="</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;

	case'getTipekaryawan':
		
		$tab="<table id=tabledt cellpadding=5 cellspacing=1  class=sortable width=100%>
			<thead><tr class=rowheader>
			<td align=center >No</td>
			<td align=center >Tipe Karyawan</td>
			<td align=center >Action</td>
		</tr></thead><tbody>";
		
		$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif = '1'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$idtipekar=$bar['id'];
		}

		if(count($res)>0){			
			$dtipe=explode(",",$idtipekar);
			foreach($dtipe as $tp){
				$dtipe[$tp]=$tp;
			}
		}

		$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by id";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td hidden align=center id=id_tipekar".$no." name=id_tipekar[]>".$bar['id']."</td>";
			$tab.="<td  align=center>".$bar['tipe']."</td>";		
			$tab.="<td align=center><input id=check".$no." name=check[] type=checkbox></td>";
		}
		
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=5><button class=mybutton onclick=addtipekaryawan('".$no."')>Add / Tambahkan</button></td>";
		$tab.="</tr>";
		$tab.="</table>";
		echo $tab;
	break;
	
	case 'addnew':

		$tab="";
		foreach($arrstatus as $key => $val){
			if($key=='1'){
				@$optstatus.="<option value=".$key." selected>".$val."</option>";							
			}else{
				@$optstatus.="<option value=".$key.">".$val."</option>";							
			}
		}

		foreach($arrtglbulan as $key => $val){
			if($key=='0'){
				@$optblntgl.="<option value=".$key." selected>".$val."</option>";							
			}else{
				@$optblntgl.="<option value=".$key.">".$val."</option>";							
			}
		}
		
		## GET Kode PT
		$arrorgdet = getOrgDetail(3);
		foreach($arrorgdet as $key=>$val){
			@$optpt.="<option value='".$key."'>".$key." - ".$val."</option>";	
		}

        ## GET Tipe karyawan yang aktif
        $str= "select * from ".$dbname.".sdm_5tipekaryawan where aktif=1";
        $res= fetchdata($str);
		foreach($res as $val){
            @$optkar.="<option value='".$val['id']."'>".$val['tipe']."</option>";	
        }

		$tab.="
			 <table border=0 cellpadding=3 cellspacing=1 >
				<tr>
					<td>".$_SESSION['lang']['kodept']."</td>
					<td>:</td>
					<td>
						<select  class='select2' style='width:200px';  id='kodept' >".$optpt."</select>
					</td>
				</tr>
				<tr>
					<td>Kode ".$_SESSION['lang']['tipekaryawan']."</td>
					<td>:</td>
					<td>
						<input style='width:197px'; type='text' id='tipekaryawan' name='tipekaryawan' readonly onclick=getTipekaryawan() class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>Jumlah Counter</td>
					<td>:</td>
					<td>
						<input style='width:197px'; type='number' id='jumlahcounter' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>Counter</td>
					<td>:</td>
					<td>
						<input style='width:197px'; type='number' id='counter' class='myinputtext' onkeypress='return tanpa_kutip(event);'>
					</td>
				</tr>
				<tr>
					<td>TMK (Tanggal Masuk Kerja)</td>
					<td>:</td>
					<td>
                        <select class='select2' style='width:200px';  id=tmk >".$optstatus."</select>
					</td>
				</tr>
				<tr>
					<td>Format Tanggal NIK</td>
					<td>:</td>
					<td>
                        <select class='select2' style='width:200px';  id=tglbulan >".$optblntgl."</select>
					</td>
				</tr>
                <tr>

				<input hidden style='width:197px'; type='number' id='id' value = '' class='myinputtext' onkeypress='return tanpa_kutip(event);'>

                    <td><input type=hidden id=method value=insert></td>
                    <td align=center colspan=4>
						<button onclick=simpan(); style='width:100px;height:30px' class=mybutton>Save</button>
                    </td>
                </tr>
            </table>";
		
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();


			if($param['kodept'] == ''){
				exit("Warning : Kode PT wajitb diisi... ");
			}

			if($param['tipekaryawan'] == ''){
				exit("Warning : Tipekaryawan wajib diisi ");
			}

			if($param['jumlahcounter'] == '' || $param['jumlahcounter'] <= 0 ){
				exit("Warning : Jumlah counter harus lebih dari 0 atau tidak boleh kosong   ");
			}

			if($param['counter'] == ''){
				exit("Warning : Counter wajib diisi ");
			}
			
			## VALIDATE
			$str="select count(kodept) as jlhitem from ".$dbname.".sdm_5formatnik where kodept='".$param['kodept']."'  and tipekaryawan in (".$param['tipekaryawan'].") ";
			$res=fetchdata($str);
			if($res[0]['jlhitem'] > 0){
				exit("Warning : Data sudah ada ");
			}
			
			$data = array(
				'kodept'		=> $param['kodept'],
				'tipekaryawan' 	=> $param['tipekaryawan'],
				'jumlahcounter' => $param['jumlahcounter'],
				'counter' 		=> $param['counter'],
				'tmk' 			=> $param['tmk'],
				'tglbulan' 		=> $param['tglbulan'],
				'updatetime'	=> date('Y-m-d H:i:s'),
				'updateby' 		=> $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$str = insertQuery($dbname,'sdm_5formatnik',$data,$cols);
			$owlPDO->exec($str);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
			
			if($param['kodept'] == ''){
				exit("Warning : Kode PT wajitb diisi... ");
			}

			if($param['tipekaryawan'] == ''){
				exit("Warning : Tipekaryawan wajib diisi ");
			}

			if($param['jumlahcounter'] == '' || $param['jumlahcounter'] <= 0 ){
				exit("Warning : Jumlah counter harus lebih dari 0 atau tidak boleh kosong   ");
			}

			if($param['counter'] == ''){
				exit("Warning : Counter wajib diisi ");
			}

			$data = array(
				'tipekaryawan'  => $param['tipekaryawan'],
				'jumlahcounter' => $param['jumlahcounter'],
				'counter' 		=> $param['counter'],
				'tmk' 			=> $param['tmk'],
				'tglbulan' 		=> $param['tglbulan'],
				'updatetime'	=> date('Y-m-d H:i:s'),
				'updateby' 		=> $_SESSION['standard']['userid']
			);
			$where = "kodept='".$param['kodept']."' and id='".$param['id']."'";
			$str = updateQuery($dbname,'sdm_5formatnik',$data,$where);
			$owlPDO->exec($str);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
}
?>

<!-- <script>
	getSelect2();
</script> -->