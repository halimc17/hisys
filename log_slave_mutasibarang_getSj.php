<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param=$_POST;
if(empty($param['gudangId'])){
	$param['gudangId'] = "";
}
function cariyangtidaksama($data,$kodebarang){
	$result = 0;
	if(isset($data[$kodebarang])){
		$result = $data[$kodebarang];
	}
	return $result;
}
$kdid=substr($param['gudangId'],0,4);
$whrt="kodeorganisasi='".substr($param['gudangId'],0,4)."'";
$optInduk=  makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', $whrt);

switch($param['proses']){
    case'list':
		$q = "select a.nosj,a.kodebarang,a.notransaksireferensi,a.jenis
              from ".$dbname.".log_suratjalandt a
              left join ".$dbname.".log_suratjalanht b on a.nosj=b.nosj
              where a.notransaksireferensi='' and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.kodept='".$optInduk[$kdid]."'" 
             . " group by nosj,jenis order by nosj desc";
        $res = fetchData($q);
        $str ="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $exist = array();
        foreach($res as $row) {
                if(!in_array($row['nosj'],$exist)) {
                        $str .= "<option value='".$row['nosj']."'>".$row['nosj']." [".$row['jenis']."]</option>";
                        $exist[$row['nosj']] = $row['nosj'];
                } 
        }
        echo $str;
    break;
    case'crLst':
	
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
        $tab.="<tr class=rowheader>";
        $tab.="<td>".$_SESSION['lang']['nomor']."</td>";
        $tab.="<td>".$_SESSION['lang']['nosj']."</td>";
        $tab.="<td>".$_SESSION['lang']['tipe']."</td></tr></thead><tbody>";
        
        if($param['txtcrNosj']!=''){
            $add="and nosj like '%".$param['txtcrNosj']."%'";
        }
        $q = "select distinct a.nosj,a.kodebarang,a.notransaksireferensi,a.jenis
              from ".$dbname.".log_suratjalandt a
              left join ".$dbname.".log_suratjalanht b on a.nosj=b.nosj
              where a.notransaksireferensi='' and b.kodeorg='".$_SESSION['empl']['lokasitugas']."' and a.kodept='".$optInduk[$kdid]."'"
              . " and a.nosj like '%".$param['txtcrNosj']."%'" 
             . "group by nosj,jenis order by nosj desc";
		$sqDt=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
		$sqDt->setFetchMode(PDO::FETCH_ASSOC);
        while($rDt=$sqDt->fetch()){
			$no+=1;
			$addTr="onclick=setNosj('".$rDt['nosj']."','".$rDt['jenis']."') title='click ".$rDt['nosj']."' style=cursor:pointer";
			$tab.="<tr class=rowcontent ".$addTr.">";
			$tab.="<td>".$no."</td>";
			$tab.="<td>".$rDt['nosj']."</td>";
			$tab.="<td>".$rDt['jenis']."</td>";
			$tab.="</tr>";
        }
        $tab.="</tbody></table>";
        echo $tab;
    break;
	case'getnopo':
		$kodeorg = "";
		$kodegudang = "";
		if(isset($param['kodegudang'])){
			$kodegudang = $param['kodegudang'];
		}
		if(isset($param['kodeorg'])){
			$kodeorg = $param['kodeorg'];
		}
		$str="select notransaksi,nopo,kodept from ".$dbname.".log_transaksiht where tipetransaksi = '1' and post = '1' and kodegudang = '".$kodegudang."' and kodept = '".$kodeorg."' order by nopo";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$optnopo="<option value=''></option>";
		while($bar=$res->fetch())
		{
			$optnopo .="<option value='".$bar->notransaksi."'>".$bar->nopo."</option>";
		}
		echo $optnopo;
	break;
	case'getsuratjalan':
		$kodept = "";
		$kodegudang = "";
		if(isset($param['gudangId'])){
			$kodegudang = $param['gudangId'];
		}
		if(isset($param['kodept'])){
			$kodept = $param['kodept'];
		}
		
		$arrsj = array();
		$arrhsl = array();
		$str="select b.nosj, a.kodebarang, a.jumlah from ".$dbname.".log_transaksidt a 
			left join ".$dbname.".log_transaksiht b on a.notransaksi=b.notransaksi
			where (b.nosj is not null and b.nosj != '-' and b.nosj!='') and b.tipetransaksi='7'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$arrsj[$val['nosj']][$val['kodebarang']]['kodebarang'] = $val['kodebarang'];
			$arrsj[$val['nosj']][$val['kodebarang']]['qty'] = $val['jumlah'];
		}
		
		$str="select * from ".$dbname.".log_suratjalandt a 
			left join ".$dbname.".log_suratjalanht b on a.nosj=b.nosj 
			where b.posting='1' and b.kodept='".$kodept."' and b.kodeorg='".$kodegudang."'";
		$res=fetchdata($str);
		foreach($res as $key=>$val){
			$selisih = 0;
			if($val['jenis']=='PL'){
				$strx="select * from ".$dbname.".log_packingdt where notransaksi='".$val['kodebarang']."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					$selisih = $valx['jumlah'] - $arrsj[$val['nosj']][$valx['kodebarang']]['kodebarang'];
					if($selisih > 0){
						$arrhsl[$val['nosj']] = $val['nosj'];
					}
				}
			}else{
				$selisih = $val['jumlah'] - $arrsj[$val['nosj']][$val['kodebarang']]['kodebarang'];
				if($selisih > 0){
					$arrhsl[$val['nosj']] = $val['nosj'];
				}
			}
		}
		
		$temp = '<option value=""></option>';
		foreach($arrhsl as $key){
			$temp .= '<option value="'.$key.'">'.$key.'</option>';
		}
		echo $temp;
	break;
    case'getbarang':
		$notransaksi = "";
		$result = array();
		if(isset($param['notransaksi'])){
			$notransaksi = $param['notransaksi'];
		}
		$gudang = "";
		if(isset($param['sloc'])){
			$gudang = $param['sloc'];
		}
		$pemilikbarang = "";
		if(isset($param['pemilikbarang'])){
			$pemilikbarang = $param['pemilikbarang'];
		}
		//ambil saldo qty===============================================
		$saldoqty=0;
		$data = array();
		$strs="select kodebarang,saldoqty from ".$dbname.".log_5masterbarangdt where kodeorg='".$pemilikbarang."'
			and kodegudang='".$gudang."'"; 
		$ress=$owlPDO->query($strs) or die(print " Gagal: ".PDOException::getMessage());
		$ress->setFetchMode(PDO::FETCH_OBJ);
		while($bars=$ress->fetch())
		{
			$data[$bars->kodebarang] = $bars->saldoqty;
		}
		
		$str="select log_suratjalandt.kodebarang,log_suratjalandt.satuanpo,log_suratjalandt.jumlah,
		log_5masterbarang.namabarang,
		log_suratjalandt.jenis from ".$dbname.".log_suratjalandt 
		left join log_5masterbarang on log_suratjalandt.kodebarang=log_5masterbarang.kodebarang
		where log_suratjalandt.nosj = '".$notransaksi."' and log_suratjalandt.jenis in ('PO','PL','M') order by log_5masterbarang.namabarang";
		$suratjalandt = fetchData($str);
		$tab = "
		<input id='suratjalan_flag' type=hidden value='true'>
		<table class=sortable cellspacing=1 border=0>
		   <thead>
		   <tr class=rowheader>
				<td align=center>".$_SESSION['lang']['kodebarang']."</td>
				<td align=center>".$_SESSION['lang']['namabarang']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center>".$_SESSION['lang']['jumlah']."</td>
				<td align=center>".$_SESSION['lang']['selisih']."</td>
				<td align=center>".$_SESSION['lang']['ket']."</td>
 		   </tr>
		   </thead><tbody id='listbarang'>";
		$trkosong = "<tr class=rowcontent><td colspan='6'>Data Kosong</td></tr>";
		$tr = "";
		$bolehkirim = "true";
		$num = 0;
		$databarang = array();
		foreach($suratjalandt as $v){
			$boleh = "true";
			if($v['jenis'] == 'PO'){
				$d = array();
				$d = $v;
				$d['bolehkirim'] = $boleh;
				if(cariyangtidaksama($data,$v['kodebarang']) < $v['jumlah']){
					$d['bolehkirim'] = "false";
				}
				$databarang[] = $d;
			}else if($v['jenis'] == 'PL'){
				$packing="select a.*,b.namabarang from ".$dbname.".log_packingdt a
				left join log_5masterbarang b on a.kodebarang = b.kodebarang
				where a.notransaksi='".$v['kodebarang']."'";
				$r = fetchData($packing);
				for($i=0; $i<count($r); $i++){
					$d = array();
					$d = $r[$i];
					$d['bolehkirim'] = $boleh;
					if(cariyangtidaksama($data,$r[$i]['kodebarang']) < $r[$i]['jumlah']){
						$d['bolehkirim'] = "false";
					}
					$databarang[] = $d;
				}
			}else if($v['jenis'] == 'M'){
				$d = array();
				$d = $v;
				$d['bolehkirim'] = $boleh;
				if(cariyangtidaksama($data,$v['kodebarang']) < $v['jumlah']){
					$d['bolehkirim'] = "false";
				}
				$databarang[] = $d;
			}
			$num++;
		}
		$i = 0;
		foreach($databarang as $k => $v){
			$trkosong = "";
			if($v['bolehkirim']  == "true"){
				$tr.= "<tr class=rowcontent>";
				$tr.="<td>".$v['kodebarang']."<input type=hidden name=kodebarang value='".$v['kodebarang']."'></td>";
				$tr.="<td>".$v['namabarang']."<input type=hidden name=namabarang value='".$v['namabarang']."'></td>";
				$tr.="<td align='center'>".$v['satuanpo']."<input type=hidden name=satuan value='".$v['satuanpo']."'></td>";
				$tr.="<td align='right'>".$v['jumlah']."<input type=hidden name=qty value='".$v['jumlah']."'></td>";
				$tr.="<td align='right'>0</td>";
				$tr.="<td align='right'></td>";
				$tr.= "</tr>";
			}else{
				$tr.= "<tr class='rowcontent' style='color:red;'>";
				$tr.="<td>".$v['kodebarang']."</td>";
				$tr.="<td>".$v['namabarang']."</td>";
				$tr.="<td align='center'>".$v['satuanpo']."</td>";
				$tr.="<td align='right'>".$v['jumlah']."</td>";
				$tr.="<td align='right'>".(cariyangtidaksama($data,$v['kodebarang'])-$v['jumlah'])."</td>";
				//if($i == 0){
				$tr.="<td align='right' >Tidak bisa, karena jumlah stok barang kurang !</td>";
				//}
				$tr.= "</tr>";
			}
			$i++;
		}
		$tab.=$trkosong;
		$tab.=$tr;
		$tab.="</tbody>";
		$tab.="</table><br>";
		$tab.="<button onclick=saveItemBast() class=mybutton>".$_SESSION['lang']['save']."</button>
				<button onclick=refresh() class=mybutton>".$_SESSION['lang']['cancel']."</button>	";
		//echo $tab;
		$result['data'] = $tab;
		$query = "select nosj,expeditor,jeniskend,driver,hpdriver,nopol from ".$dbname.".log_suratjalanht where nosj = '".$notransaksi."' limit 1";
		$tmpData = fetchData($query);
		$result['expeditor'] 	= $tmpData[0]['expeditor'];
		$result['jeniskend'] 	= $tmpData[0]['jeniskend'];
		$result['driver'] 		= $tmpData[0]['driver'];
		$result['hpdriver'] 	= $tmpData[0]['hpdriver'];
		$result['nopol'] 		= $tmpData[0]['nopol'];
		echo json_encode($result);
    break;
	case 'defaulttemplatedetail':
		$tab = "<table class=sortable cellpadding=3 cellspacing=1 border=0>
				   <thead>
				   <tr class=rowheader style=height:25px>
					<th align=center>".$_SESSION['lang']['kodebarang']."</th>
					<th align=center>".$_SESSION['lang']['namabarang']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['saldo']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<!--<th align=center>".$_SESSION['lang']['nopo']."</th>-->
					<th align=center>".$_SESSION['lang']['file']."</th>
				   </tr>
				   </thead>
					<tbody id='listbarang'>
							<tr class=rowcontent>
							 <td><input type=text size=15 maxlength=10 id=kodebarang name=kodebarang class=myinputtext onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."',event);\"></td>
								 <td><input type=text size=65 maxlength=100 id=namabarang name=namabarang class=myinputtext readonly onclick=\"showWindowBarang('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."',event);\"></td>
								 <td><input type=text size=8 maxlength=5 id=satuan name=satuan class=myinputtext  onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."',event);\"></td>
								 <td><input type=text size=10 maxlength=15 id=saldoakhirqty name=saldoakhirqty class=myinputtext  onkeypress=\"return false;\" onclick=\"showWindowBarang('".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."',event);\"></td>
								 <td><input type=text size=10 maxlength=15 id=qty name=qty class=myinputtextnumber onkeypress=\"return angka_doang(event);\"></td>
								 <!--<td>
								 	<select id='nopo' name='nopo' style='width:165px' disabled><option hidden>Pilih Gudang Dahulu</option></select>
								 	<img onclick=z.elSearch('nopo',event) class='resicon' src='images/onebit_02.png' style=''>
								 </td>-->
								<td align=center><button class=mybutton onclick=showupload(event)>Upload Files</button></td>
							</tr>			   
					</tbody>
				   <tfoot>
					<tr>
						<td colspan=6 align=right>						
							<button onclick=saveItemBast() class=mybutton id=saveitemmutasi>".$_SESSION['lang']['save']."</button>
							<button onclick=EditItemBast() class=mybutton  id=edititemmutasi style='display:none;'>".$_SESSION['lang']['save']."</button>
							<button onclick=nextItem() class=mybutton>".$_SESSION['lang']['cancel']."</button>	
							<button onclick=bastBaru() class=mybutton>".$_SESSION['lang']['done']."</button>	
						</td>
					</tr>
				   </tfoot>
			</table>
			";
		echo $tab;
	break;
	//Umar
	case 'getNopo':
		$optNopo = "<option hidden>Pilih Data</option>";
		$query 	 = "SELECT nopo FROM ".$dbname.".log_poht WHERE kodeorg = '".$param['pt']."'";
		$res     = fetchData($query);
		foreach ($res as $key => $value) {
			$optNopo .= '<option value="'.$value['nopo'].'">'.$value['nopo'].'</option>';
		}

		echo $optNopo;
	break;
	case 'loadMaterial':
		//ambil data untuk ditampilkan
		$strj="select a.* from ".$dbname.".log_transaksidt a 
			   where a.notransaksi='".$param['nodok']."' order by waktutransaksi asc ";	
		$resj=$owlPDO->query($strj) or die(print " Gagal: ".PDOException::getMessage());
		$resj->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
	 	while($barj=$resj->fetch()) {
	        $no+=1;

	        //ambil namabarang
	        $namabarangk = '';
	        $strk 		 = "select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$barj->kodebarang."'";
			$resk 		 = $owlPDO->query($strk) or die(print " Gagal: ".PDOException::getMessage());
			$resk->setFetchMode(PDO::FETCH_OBJ);
	        while($bark = $resk->fetch()){
	            $namabarangk = $bark->namabarang;
	        }

	        echo"<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center>".$barj->kodebarang."</td>
					<td>".$namabarangk."</td>
					<td align=center>".$barj->satuan."</td>
					<td align=right>".numberformat_kasih_koma($barj->jumlah)."</td>
					<td align=center style='display:none'>".$barj->nopo."</td>
					<td align=center width=25px><img src=images/application/application_edit.png class=resicon  title='edit' onclick=\"editMutasi('".$barj->kodebarang."','".$namabarangk."','".$barj->satuan."','".$barj->jumlah."','".$barj->nopo."');\"></td>
					<td align=center width=25px><img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delMutasi('".$param['nodok']."','".$barj->kodebarang."');\">
					</td>
				</tr>";
		}
	break;
	//End Umar
}
