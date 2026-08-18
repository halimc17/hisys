<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$id       = checkPostGet('id', '');
$kodeorg  = checkPostGet('kodeorg', '');
$periode  = checkPostGet('periode', '');
$tt       = checkPostGet('tt', '');
$jenishari= checkPostGet('jenishari', '');
$basisha  = checkPostGet('basisha', '');
$basiskg  = checkPostGet('basiskg', '');
$premilebihbasis = checkPostGet('premilebihbasis', '');
$premibrondolan  = checkPostGet('premibrondolan', '');
$premikesulitan  = checkPostGet('premikesulitan', '');
$premikehadiran  = checkPostGet('premikehadiran', '');
$banjir   = checkPostGet('banjir', '');

$param = $_POST;if(count($param)==0){$param = $_GET;}
$arrHari = array(
	'KERJA' => 'Hari Kerja',
	'HL' => 'Hari Minggu',
    'LN' => 'Hari Libur Nasional'
);

switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$where = " id='".$param['id']."'";
			$str = "delete from " . $dbname . ".kebun_5basispanen3 where ".$where."";
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
					'kodeorg'         => $param['kodeorg'],
                    'periode'         => $param['periode'],
                    'tahuntanam'      => $param['tt'],
                    'jenishari'       => $param['jenishari'],
                    'basisha'         => $param['basisha'],
                    'basiskg'         => $param['basiskg'],
                    'premilebihbasis' => $param['premilebihbasis'],
                    'premibrondolan'  => $param['premibrondol'],
                    'premikesulitan'  => $param['premikesulitan'],
                    'premikehadiran'  => $param['premikehadiran'],
                    'banjir'   	      => $param['banjir'],
                    'updateby'        => $_SESSION['standard']['userid'],
				);
				$where = "id='".$param['id']."'";
				$query = updateQuery($dbname,'kebun_5basispanen3',$data,$where);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':

        $str="select * from ".$dbname.".kebun_5basispanen3 where kodeorg ='".$param['kodeorg']."' and periode='".$param['periode']."' and tahuntanam = '".$param['tt']."' and jenishari = '".$param['jenishari']."' ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs>0){   
            exit("Warning :  Data sudah ada ");
        }

		try {
			$owlPDO->beginTransaction();
			$data = array(
                'kodeorg'         => $param['kodeorg'],
				'periode'         => $param['periode'],
				'tahuntanam'      => $param['tt'],
				'jenishari'       => $param['jenishari'],
				'basisha'         => $param['basisha'],
				'basiskg'         => $param['basiskg'],
				'premilebihbasis' => $param['premilebihbasis'],
				'premibrondolan'    => $param['premibrondol'],
				'premikesulitan'  => $param['premikesulitan'],
				'premikehadiran'  => $param['premikehadiran'],
				'banjir'   	      => $param['banjir'],
				'createby'        => $_SESSION['standard']['userid'],
				'createdate'      => date("Y-m-d H:i:s"),
				'updateby'        => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'kebun_5basispanen3',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'gettahuntanam':
		$opttt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select distinct tahuntanam from ".$dbname.".setup_blok where 1=1 and kodeorg like '".$param['kodeorg']."%' and tahuntanam>0 order by tahuntanam asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$opttt.="<option value=".$bar['tahuntanam'].">".$bar['tahuntanam']."</option>";
		}
		
		echo $opttt;
	break;
	case 'addnew':
		$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach(getOrgDetail(23) as $key => $val){
			$d=getNamaOrg($key,'induk');
			if($d!=$n){			
				$optkodeorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
			}
			$optkodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
			$n=$d;
			if($d!=$n){			
				$optkodeorg.="</optgroup>";
			}
		}
		
		for($x=-2;$x<12;$x++){
			$dt=mktime(0,0,0,date('m')-$x,12,date('Y'));
			$prd[date("Y-m",$dt)]=date("Y-m",$dt);
		}
		
		array_multisort($prd,SORT_DESC);
		$optprd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($prd as $bar){	
			$optprd.="<option value=".$bar.">".$bar."</option>";
		}
		
		$opthari="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($arrHari as $row => $val) {
			$opthari.="<option value=".$row.">".$val."</option>";
		}

		$optBanjir="<option value='0'>TIDAK</option>";
		$optBanjir.="<option value='1'>YA</option>";
		
		$opttt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>".$_SESSION['lang']['kebun']."</td>
					<td><select class='select2' onchange=gettahuntanam(); style='width:400px;' id=kodeorg >".$optkodeorg."</select></td>
				</tr>
                <tr>
					<td style=min-width:150px>".$_SESSION['lang']['periode']." Berlaku</td>
					<td><select class='select2' style='width:400px;' id=periode >".$optprd."</select></td>
				</tr>
				<tr>
					<td style=min-width:150px>".$_SESSION['lang']['tahuntanam']."</td>
					<td><select class='select2' style='width:400px;' id=tt >".$opttt."</select></td>
				</tr>
				<tr>
					<td>Jenis ".$_SESSION['lang']['hari']."</td>
					<td><select class='select2' style='width:400px;' id=jenishari >".$opthari."</select></td>
				</tr>
                <tr>
					<td>Basis (HA)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='basisha' onkeypress=\"return isNumberKey(event);\" placeholder=0></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['norma']."</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='basiskg' onkeypress=\"return isNumberKey(event);\" placeholder=0></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premi']." Lebih Basis (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premilebihbasis' onkeypress=\"return isNumberKey(event);\" placeholder=0></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premi']." Brondol (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premibrondol' onkeypress=\"return isNumberKey(event);\" placeholder=0></td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['premi']." Kesulitan (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premikesulitan' onkeypress=\"return isNumberKey(event);\" placeholder=0></td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['premi']." Kehadiran (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premikehadiran' onkeypress=\"return isNumberKey(event);\" placeholder=0></td>
				</tr>
				<tr>
					<td>Banjir</td>
					<td><select class='select2' style='width:400px;' id='banjir' >".$optBanjir."</select></td>
				</tr>
                <tr>
                    <td colspan=4 align=center>
						<input type=hidden id=id >
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>";
		echo $tab;
	break;
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['kodeorg']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['periode']." Berlaku</th>
				<th style='text-align:center;'>".$_SESSION['lang']['tahuntanam']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['hari']."</th>
				<th style='text-align:center;'>Basis (HA)</th>
				<th style='text-align:center;'>".$_SESSION['lang']['norma']."</th>
				<th style='text-align:center;'>".$_SESSION['lang']['premibasis']." (Rp)</th>
				<th style='text-align:center;'>".$_SESSION['lang']['premi']." Brondol (Rp)</th>
				<th style='text-align:center;'>Premi Kesulitan (Rp)</th>
				<th style='text-align:center;'>Premi Kehadiran</th>
				<th style='text-align:center;'>Banjir</th>
				<th style='text-align:center;'>".$_SESSION['lang']['action']."</th>
			</tr>
		</thead>
		<tbody >";
		
		$where=" and substr(kodeorg,1,4) in (".getOrgDetail(2).")";
		$str = "select * from ".$dbname.".kebun_5basispanen3 where 1=1 ".$where." order by periode desc, kodeorg asc, jenishari asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;' nowrap>".getNamaOrg($bar['kodeorg'])."</td>";
			$tab.="<td style='text-align:center;'>".$bar['periode']."</td>";
			$tab.="<td style='text-align:center;'>".$bar['tahuntanam']."</td>";
			$tab.="<td style='text-align:center;' nowrap>".$arrHari[$bar['jenishari']]."</td>";

			$tab.="<td style='text-align:center;'>".number_format($bar['basisha'],2)."</td>";
			$tab.="<td style='text-align:center;'>".number_format($bar['basiskg'],2)."</td>";
			$tab.="<td style='text-align:center;'>".number_format($bar['premilebihbasis'],2)."</td>";
			$tab.="<td style='text-align:center;'>".number_format($bar['premibrondolan'],2)."</td>";
			$tab.="<td style='text-align:center;'>".number_format($bar['premikesulitan'],2)."</td>";
			$tab.="<td style='text-align:center;'>".number_format($bar['premikehadiran'],2)."</td>";

            if($bar['banjir'] == 1){
				$banjir ="YA";
			}else{
				$banjir ="TIDAK";
			}

			$tab.="<td style='text-align:center;'>".$banjir."</td>";		

	        $tab .= "<td style='text-align:center;width:25px'>
                <img src='images/application/application_edit.png' class='resicon' title='Edit' 
                 onclick=\"editdata('edit','".$bar['id']."','".$bar['kodeorg']."','".$bar['periode']."','".$bar['tahuntanam']."','".$bar['jenishari']."','".$bar['basisha']."','".$bar['basiskg']."','".$bar['premilebihbasis']."','".$bar['premibrondolan']."','".$bar['premikesulitan']."','".$bar['premikehadiran']."','".$bar['banjir']."')\">
			
            </td>";            
            $tab.="</tr>";
		}
		
		$tab.="</tbody>
		</table>";
		echo $tab;
	break;
	case 'showEditDialog':
		$optBanjir="<option value='0' ".($banjir==0?'selected':'').">TIDAK</option>";
		$optBanjir.="<option value='1' ".($banjir==1?'selected':'').">YA</option>";

		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style='min-width:150px;'>". $_SESSION['lang']['kebun'] ."</td>
					<td>
						<select class='select2' style='width:400px;' id='kodeorg' name=kodeorg' disabled>
							<option value='". $kodeorg ."' selected>
								". getNamaOrg($kodeorg) ."
							</option>
						</select>
					</td>
				</tr>

                <tr>
					<td style=min-width:150px>".$_SESSION['lang']['periode']." Berlaku</td>
					<td>
						<select class='select2' style='width:400px;' id=periode disabled >
							<option value='" . $periode . "' selected>
								" . $periode . "
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>".$_SESSION['lang']['tahuntanam']."</td>
					<td>
						<select class='select2' style='width:400px;' id=tt disabled >
							<option value='" . $tt . "' selected>
								" . $tt . "
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Jenis ".$_SESSION['lang']['hari']."</td>
					<td>
						<select class='select2' style='width:400px;' id=jenishari disabled >
							<option value='" . $jenishari . "' selected>
								" . $arrHari[$jenishari] . "
							</option>
						</select>
					</td>
				</tr>
                <tr>
					<td>Basis (HA)</td>
					<td>
						<input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='basisha' onkeypress=\"return isNumberKey(event);\" placeholder=0 value=" . $basisha . ">
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['norma']."</td>
					<td>
						<input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='basiskg' onkeypress=\"return isNumberKey(event);\" placeholder=0 value=" . $basiskg . ">
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premi']." Lebih Basis (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premilebihbasis' onkeypress=\"return isNumberKey(event);\" placeholder=0 value=" . $premilebihbasis . "></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premi']." Brondol (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premibrondol' onkeypress=\"return isNumberKey(event);\" placeholder=0 value=". $premibrondolan ."></td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['premi']." Kesulitan (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premikesulitan' onkeypress=\"return isNumberKey(event);\" placeholder=0 value=" . $premikesulitan . "></td>
				</tr>
                <tr>
					<td>".$_SESSION['lang']['premi']." Kehadiran (Rp)</td>
					<td><input class=myinputtextnumber onclick=delnol(this); style='text-align:right;width:378px;height:30px;font-size:14px;padding-right:10px;padding-left:10px' type=text id='premikehadiran' onkeypress=\"return isNumberKey(event);\" placeholder=0 value=" . $premikehadiran . "></td>
				</tr>
				<tr>
					<td>Banjir</td>
					<td><select class='select2' style='width:400px;' id='banjir' >".$optBanjir."</select></td>
				</tr>
                <tr>
                    <td colspan=4 align=center>
						<input type=hidden id=id value=" . $id . ">
						<input type=hidden id=method value=update>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>";
		echo $tab;
	break;
}
?>
