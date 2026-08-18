<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}


$str = "select *  from " . $dbname . ".keu_5akun";
$res = fetchdata($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=$bar['namaakun'];
	$tipeakun[$bar['noakun']]=$bar['tipeakun'];
}
$str = "select *  from " . $dbname . ".setup_klpkegiatan";
$res = fetchdata($str);
foreach($res as $bar){
	$nmkel[$bar['kodeklp']]=$bar['namakelompok'];
}


$str = "select *  from " . $dbname . ".organisasi where induk=''";
$res = fetchdata($str);
foreach($res as $bar){
	$holding=$bar['kodeorganisasi'];
}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
$arrpremi=array('1'=>'Premi di BKM dikunci','0'=>'Premi di BKM tidak dikunci');

switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$str = "select count(*) as jlh  from " . $dbname . ".keu_jurnaldt where kodekegiatan='".$param['kodekeg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode kegiatan sudah dipakai di jurnal.");
				}
			}


			$str = "select count(*) as jlh  from " . $dbname . ".kebun_prestasi where kodekegiatan='".$param['kodekeg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode kegiatan sudah dipakai di BKM.");
				}
			}
			$str = "select count(*) as jlh  from " . $dbname . ".log_transaksidt where kodekegiatan='".$param['kodekeg']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['jlh']>0){
					throw new PDOException("Kode kegiatan sudah dipakai di Gudang.");
				}
			}
			
			
			$where = "and kodeorg='".$holding."' and kodekegiatan='".$param['kodekeg']."' and kelompok='".$param['kelompok']."'";
			$str = "delete from " . $dbname . ".setup_kegiatan where 1=1 ".$where."";
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
					'namakegiatan' => $param['nmkegid'],
					'namakegiatan1'=> $param['nmkegen'],
					'satuan'       => $param['satuan'],
					'pilihanluas'       => $param['pilihanluas'],
					'status'       => $param['status'],
					'premi'        => $param['premi']
				);
				$where = "kodeorg='".$holding."' and kodekegiatan='".$param['kodekeg']."' and kelompok='".$param['kelompok']."'";
				$query = updateQuery($dbname,'setup_kegiatan',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);

				// Cek Apakah Ada Kode Kegiatan Kebun Di Setup Kegiatan Traksi
				$scek = selectQuery($dbname,"vhc_kegiatan","*","setupkegiatan='{$param['kodekeg']}'");
				$rcek = fetchData($scek);
				if (count($rcek) > 0) {
					// Jika ada, maka updatekan statusnya sama seperti setup kegiatan kebun berdasarkan kode kegiatan traksi 
					// yang asal setup kegiatan kebunnya seperti di paramater 
					$datatrk = array(
						'status' => $param['status']
					);
					$wheretrk = "setupkegiatan='{$param['kodekeg']}'";
					$sqltrk = updateQuery($dbname,"vhc_kegiatan",$datatrk,$wheretrk);
					$owlPDO->exec($sqltrk);
				}
				// throw new PDOException("TESS");
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'kodeorg'      => $holding,
				'kodekegiatan' => $param['kodekeg'],
				'namakegiatan' => $param['nmkegid'],
				'namakegiatan1'=> $param['nmkegen'],
				'kelompok'     => $param['kelompok'],
				'satuan'       => $param['satuan'],
				'pilihanluas'       => $param['pilihanluas'],
				'noakun'       => $param['noakun'],
				'status'       => $param['status'],
				'premi'        => $param['premi']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'setup_kegiatan',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'getkegiatan':
		$str = "select max(kodekegiatan) as kodekegiatan from ".$dbname.".`setup_kegiatan` where kodekegiatan like '".$param['noakun']."%'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$kodekegiatan=$bar['kodekegiatan'];
		}
		
		$kodekeg = substr($bar['kodekegiatan'],-2);
		if(count($res)==0){
			$urut='01';
		}else{
			$urut=addZero((intval($kodekeg)+1),2);
		}
		
		echo $kegiatan = $param['noakun'].$urut;
	break;	
	case 'getnoakun':
		$str = "select *  from " . $dbname . ".setup_klpkegiatan where kodeklp='".$param['kelompok']."' and kodeorg='".$holding."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$akun2=$bar['noakun'];
		}
		
		$arrnoakun=explode(',',$akun2);
		$jumlahnoakun=count($arrnoakun);
		// exit("Error:$jumlahnoakun");
		$nourut=0;
		foreach($arrnoakun as $dtakun){
			
			
			if($nourut>'0'){
				@$whereakun.=" or noakun like '".$dtakun."%' ";
			}else{
				@$whereakun.=" and noakun like '".$dtakun."%' ";
			}
			$nourut++;
		}
		
	
		$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".keu_5akun where 1=1 ".$whereakun." and namaakun not like '%non aktif%' and aktif='1'";
		// echo $str;exit("error:");
		$res = fetchData($str);
		$d='';
		foreach($res as $val){
			if(strlen($val['noakun'])==3){
				$d=$val['noakun'];
				if($d!=$n){			
					$optakun.="<optgroup label='".$d." - ".$val['namaakun']."'>";
				}
			}
			if(strlen($val['noakun'])==7){
				$sel="";
				if($param['valakun']==$val['noakun']){$sel="selected";}
				$optakun.="<option value=".$val['noakun']." ".$sel.">".$val['noakun']." - ".$val['namaakun']."</option>";			
			}
			
			if($d!=$n){			
				$n=$d;
				$optakun.="</optgroup>";
			}
		}
		
		echo $optakun;
	break;
	case 'addnew':
		$optkel.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";			
		if($_SESSION['language']=='EN'){
			$qKlp = selectQuery($dbname,'setup_klpkegiatan','kodeklp,namakelompok1,noakun',"kodeorg='".$holding."'","namakelompok1");
			$resKlp = fetchData($qKlp);
			foreach($resKlp as $row) {
				$optkel.="<option value=".$row['kodeklp'].">".$row['namakelompok1']."</option>";			
			}
		}else{
			$qKlp = selectQuery($dbname,'setup_klpkegiatan','kodeklp,namakelompok,noakun',"kodeorg='".$holding."'","namakelompok");
			$resKlp = fetchData($qKlp);
			foreach($resKlp as $row) {
				$optkel.="<option value=".$row['kodeklp'].">".$row['namakelompok']."</option>";			
			}
		}

		$optakun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".keu_5akun";
		$res = fetchData($str);
		foreach($res as $val){
			if(strlen($val['noakun'])==3){
				$d=$val['noakun'];
				if($d!=$n){			
					$optakun.="<optgroup label='".$d." - ".$val['namaakun']."'>";
				}
			}
			if(strlen($val['noakun'])==7){
				$optakun.="<option value=".$val['noakun'].">".$val['noakun']." - ".$val['namaakun']."</option>";			
			}
			
			$n=$d;
			if($d!=$n){			
				$optakun.="</optgroup>";
			}
		}
		
		$optsatuan="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".setup_satuan order by satuan asc";
		$res = fetchData($str);
		foreach($res as $val){
			$optsatuan.="<option value=".strtoupper($val['satuan']).">".strtoupper($val['satuan'])."</option>";			
		}
		
		foreach($arrstatus as $key => $val){
			$optstatus.="<option value=".$key.">".$val."</option>";			
		}
		
		$arrpremi=array('1'=>'Premi di BKM dikunci','0'=>'Premi di BKM tidak dikunci');
		foreach($arrpremi as $key => $val){
			$optpremi.="<option value=".$key.">".$val."</option>";			
		}

		$opttipeluasan="<option value='0'>-</option>";
		$opttipeluasan.="<option value='1'>Luas bloking</option>";
		$opttipeluasan.="<option value='2'>Luas lahan sudah dilc</option>";
		$opttipeluasan.="<option value='3'>Luas lahan sudah ditanam</option>";

		
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>".$_SESSION['lang']['kelompokkegiatan']."</td>
					<td><select class='select2' style='width:350px;' onchange=getnoakun(this.value); id=kelkeg >".$optkel."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['noakun']."</td>
					<td><select class='select2' style='width:350px;' onchange=getkegiatan(this.value,''); id=noakun >".$optakun."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['kodekegiatan']."</td>
					<td><input class=myinputtext disabled style='width:345px;height:30px;font-size:14px;' id=kodekeg ></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namakegiatan']." [ID]</td>
					<td><input class=myinputtext style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=nmkegid ></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namakegiatan']." [EN]</td>
					<td><input class=myinputtext style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=nmkegen ></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td><select class='select2' style='width:350px;' id=satuan >".$optsatuan."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['pilihanluas']."</td>
					<td><select class='select2' style='width:350px;' id=pilihanluas >".$opttipeluasan."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premi']."</td>
					<td><select class='select2' style='width:350px;' id=premi >".$optpremi."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['status']."</td>
					<td>
						<select class='select2' style='width:350px;' id=status >".$optstatus."</select>
						<input type='hidden' id='oldstatus' value=''>
					</td>
				</tr>
				
                <tr>
                    <td><input type=hidden id=method value=insert></td>
                    <td colspan=4>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'loaddata':
		$opttipeluasan['0']='-';
		$opttipeluasan['1']='Luas bloking';
		$opttipeluasan['2']='Luas lahan sudah dilc';
		$opttipeluasan['3']='Luas lahan sudah ditanam';

		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['noakun']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['namaakun']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kodekegiatan']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['namakegiatan']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['kelompok']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['satuan']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['pilihanluas']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['premi']."</th>
				<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['status']."</th>
				<th style='text-align:center;' colspan=3>".$_SESSION['lang']['action']."</th>
			</tr>
			<tr class=rowheader>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
				<th  style='display:none;'></th>
			</tr>
		</thead>
		<tbody >";
		
		

		$str= "select * from ".$dbname.".setup_kegiatan order by kodekegiatan asc";
		$res= fetchdata($str);
		foreach($res as $bar){
			$e=substr($bar['noakun'],0,3);
			if($e!=$o){
				$sqe= "select * from ".$dbname.".keu_5akun where noakun = '".substr($bar['noakun'],0,3)."' order by noakun asc";
				$ree= fetchdata($sqe);
				foreach($ree as $vae){
					$no+=1;
					$tab.="<tr class=rowcontent style=background-color:#e8e8e8>";
					$tab.="<td style='text-align:center;'>".$no."</td>";
					$tab.="<td style='text-align:left;'>".$vae['noakun']."</td>";
					$tab.="<td style='text-align:left;'>".$vae['namaakun']."</td>";
					$tab.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
					
					$tab.="</tr>";
				}
			}
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$bar['noakun']."</td>";
			$tab.="<td style='text-align:left;'>".$nmakun[$bar['noakun']]."</td>";
			$tab.="<td style='text-align:center;'>".$bar['kodekegiatan']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['namakegiatan']."</td>";
			$tab.="<td style='text-align:left;'>".$nmkel[$bar['kelompok']]."</td>";
			$tab.="<td style='text-align:center;'>".$bar['satuan']."</td>";
			$tab.="<td style='text-align:center;'>".$opttipeluasan[$bar['pilihanluas']]."</td>";
			$tab.="<td style='text-align:left;'>".$arrpremi[$bar['premi']]."</td>";
			$tab.="<td style='text-align:left;'>".$arrstatus[$bar['status']]."</td>";
			
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('edit','".$bar['noakun']."','".$bar['kodekegiatan']."','".$bar['namakegiatan']."','".$bar['namakegiatan1']."','".$bar['kelompok']."','".$bar['satuan']."','".$bar['pilihanluas']."','".$bar['premi']."','".$bar['status']."')\";></td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['kelompok']."','".$bar['kodekegiatan']."');></td>";
			$tab.="<td style='text-align:center;width:25px'>
				<img src='images/zoom.png' class='resicon' title='Preview' onclick=\"showNorma('".$bar['kodeorg']."','".$bar['kodekegiatan']."','".$bar['kelompok']."','".$bar['namakegiatan']."','".$bar['satuan']."')\";></td>";
					
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
