<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');
include_once('lib/zFunction.php');
use Dompdf\Dompdf;

$param = $_POST;
if(count($param)==0){$param = $_GET;	}
$id       = checkPostGet('id','');
$method   = checkPostGet('method','');
$tipe     = checkPostGet('tipe','');
$nama     = checkPostGet('nama','');
$dept     = checkPostGet('dept','');
$tglnilai = tanggalsystemn(checkPostGet('tglnilai',''));
$thnnilai = checkPostGet('thnnilai','');
$kekuatan = checkPostGet('kekuatan','');
$kelemahan= checkPostGet('kelemahan','');
$tipeprint= checkPostGet('tipeprint','');
$jab      = getPostingJabatan('disiplin'); 
$noid = checkPostGet('noid', '');
$jenispersetujuan = 'DSPLN';
$jlh = checkPostGet('jlh', '');
$notransaksi = checkPostGet('notransaksi', '');
$stsapprv = array('0'=> $_SESSION['lang']['wait_approval'],'1'=> $_SESSION['lang']['disetujui'],'2'=> $_SESSION['lang']['ditolak'],'3'=>'Dikoreksi','9'=>$_SESSION['lang']['pengajuan']);

switch ($method) {
	case 'ambilkary':
		$optdata="<option value=''>Pilih Data</option>";
		if($param['unit']!='' and $param['thnnilai']!=''){
			$str = "SELECT * FROM ".$dbname.".datakaryawan WHERE lokasitugas='".$param['unit']."' and tanggalmasuk<'".$param['thnnilai']."-01-01' and (tanggalkeluar>'".$param['thnnilai']."-01-01' or tanggalkeluar='0000-00-00' or tanggalkeluar='') and tipekaryawan=0; ";
			$res = fetchdata($str);
			foreach ($res as $val) {
				$optdata .= "<option value='".$val['karyawanid']."'>".$val['nik']." - ".$val['namakaryawan']."</option>";
			}

		}

		echo $optdata;
	break;
	case 'kalkulasiyok':
		// echo 'masuk cuk';
		// exit('Error');
		$bobot=1;
		if($param['bobot']!=''){
		// 	if($param['tipebobot']==0){
				$bobot=doubleval($param['bobot']);
		// 	}else{
		// 		$bobot=(doubleval($param['bobot']/100));
		// 	}
		}

		if($param['kodetotaloperator']==''){
			if($param['totaloperator']!=''){
				switch ($param['totaloperator']) {
					case '0':
						$total='';
						for ($i=0; $i < $param['total']; $i++) { 
							if($total==''){
								$total=$param['hasilarr'.$i];
							}else{
								$total=$total-$param['hasilarr'.$i];
							}
						}
						$total=$total*$bobot;
					break;

					case '1':
						$total='';
						for ($i=0; $i < $param['total']; $i++) { 
							if($total==''){
								$total=$param['hasilarr'.$i];
							}else{
								$total=$total+$param['hasilarr'.$i];
							}
						}
						$total=$total*$bobot;
					break;

					case '2':
						$total='';
						for ($i=0; $i < $param['total']; $i++) { 
							//echo $param['hasilarr'.$i].'xxx';
							if($total==''){
								$total=$param['hasilarr'.$i];
							}else{
								if($total==0 or $param['hasilarr'.$i]==0){
									$total=0;
								}else{
									$total=$total/$param['hasilarr'.$i];
								}
							}
						}
						// echo 'ZZZZ'.$bobot;
						// exit('error');
						$total=round(($total*$bobot),2);
					break;

					case '3':
						$total='';
						for ($i=0; $i < $param['total']; $i++) { 
							if($total==''){
								$total=$param['hasilarr'.$i];
							}else{
								$total=$total*$param['hasilarr'.$i];
							}
						}
						$total=$total=round(($total*$bobot),2);
					break;

					case '4':
						$total='';
						for ($i=0; $i < $param['total']; $i++) { 
							if($total==''){
								$total=$param['hasilarr'.$i];
							}else{
								$total=$total+$param['hasilarr'.$i];
							}
						}
						$total=$total/$param['total'];
						$total=$total=round(($total*$bobot),2);

					break;

				}

			}elseif($param['totaloperator']=='' and $param['bobot']!=''){
				$total=$bobot*$param['hasilarr0'];
			}
		}else{
			$str = "SELECT * FROM ".$dbname.".sdm_5setupscore WHERE judul='".$param['kodetotaloperator']."' and nilaidari<='".$param['hasilarr0']."' and nilaisampai>='".$param['hasilarr0']."'";
			$res = fetchdata($str);
			$total=$res[0]['nilai'];
		}

		echo $total;
	break;
	case 'kalkulasinilai1':
		$return=0;

		if($param['nilai']!=''){
				$return=doubleval($param['nilai'])*doubleval($param['bobot']);
			
		}

		if($return==-0){
			$return=0;
		}
		echo $return;
	break;
	case 'getDept':
		$str = "SELECT * FROM ".$dbname.".datakaryawan WHERE karyawanid='".$nama."'";
		$res = fetchdata($str);

		echo $res[0]['bagian']."####".$res[0]['kodejabatan']."####".$res[0]['lokasitugas'];
	break;
	case 'insert':
		try {
            $owlPDO->beginTransaction();
			$str = "SELECT * FROM ".$dbname.".sdm_disiplin WHERE unit='".$param['unit']."' and tahun='".$param['thnnilai']."' and karyawanid='".$param['karyawanid']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Penilaian Disiplin untuk karyawan ".getNamaKaryawan($param['karyawanid'])." pada tahun ".$param['thnnilai']." Sudah ada.");	
			}

			$noid=$param['thnnilai']."/".$param['unit']."/".$param['karyawanid'];
			
            $data = array(
				'noid'   	   =>$noid,
				'unit'   	   =>$param['unit'],
				'tahun'        =>$param['thnnilai'],
				'karyawanid'   =>$param['karyawanid'],
				'tanggal'      =>tanggalsystemn($param['tglnilai']),
				'updatetime'   =>date('Y-m-d H:i:s'),
				'updateby'     =>$_SESSION['standard']['userid']
			);

           	$queryH = insertQuery($dbname,'sdm_disiplin',$data,array_keys($data)); #exit("error".$queryH);
			$owlPDO->exec($queryH);
         
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warningcode : " . addslashes($e->getMessage());
        }
	break;
	case'loaddatadetail':
			$tab="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
				<thead>
					<tr class=rowheader>
						<th align=center>Keterangan</th>
						<th align=center>Nilai</th>
						<th align=center>Bobot/Pengali</th>
						<th align=center>Hasil</th>
					</tr>
				</thead>
				<tbody>";
			
			$datadata=array();
			$dataurut=array();


			// print_r($datanilai);
			// exit();
			$str = "select * from ".$dbname.".sdm_5disiplin where tahun='".$param['thnnilai']."' order by nourut asc";
			$res = fetchdata($str);
			foreach($res as $val){
				$datadata[$val['nourut']]['noidtext']=$val['id'];
				$datadata[$val['nourut']]['tipe']=$val['tipe'];
				$datadata[$val['nourut']]['text']=$val['text'];
				$dataurut[$val['nourut']]=$val['text'];
				$datadata[$val['nourut']]['tipenilai']=$val['tipenilai'];
				$datadata[$val['nourut']]['kodetipenilai']=$val['kodetipenilai'];
				$datadata[$val['nourut']]['bobot']=$val['bobot'];
				$datadata[$val['nourut']]['nouruttotal']=$val['nouruttotal'];
				$datadata[$val['nourut']]['totaloperator']=$val['totaloperator'];
				$datadata[$val['nourut']]['operatornilai']=$val['operatornilai'];
				$datadata[$val['nourut']]['kodetotaloperator']=$val['kodetotaloperator'];
			}

				$datakeytotal=array();
				$datatotal=array();
				$dataxx=array();
				$jlhx=0;
			foreach ($dataurut as $key => $val) {
				$jlhx++;
				if($datadata[$key]['nouruttotal']!=''){
					$arrdata=explode(',', $datadata[$key]['nouruttotal']);
					foreach ($arrdata as $urut=> $data) {
						if(!isset($datakeytotal[$data])){
							$datakeytotal[$data]="'".$key."'";
						}else{
							$datakeytotal[$data].=",'".$key."'";
						}
					}
				}
			}

			$dataedit=array();
			$str = "select * from ".$dbname.".sdm_disiplindt where noidht='".$param['noid']."' order by nilai asc";
			$res = fetchdata($str);
			foreach($res as $val){
				$dataedit[$val['noidtext']]['nilai']=$val['nilai'];
				$dataedit[$val['noidtext']]['hasil']=$val['hasil'];

			}
			

			$datanilai=array();
			$str = "select * from ".$dbname.".sdm_5setuppenilaian where status='1' order by nilai asc";
			$res = fetchdata($str);
			foreach($res as $val){
				for ($i=1; $i <=$jlhx; $i++) { 
					if(isset($datanilai[$i][$val['judul']])){
						if($datadata[$i]['kodetipenilai']!='' and $dataedit[$datadata[$i]['noidtext']]['nilai']==$val['nilai']){
							$datanilai[$i][$val['judul']].="<option selected value='".$val['nilai']."'>".$val['text']." (".$val['nilai'].")</option>";
						}else{
							$datanilai[$i][$val['judul']].="<option value='".$val['nilai']."'>".$val['text']." (".$val['nilai'].")</option>";
						}
					}else{
						if($datadata[$i]['kodetipenilai']!='' and $dataedit[$datadata[$i]['noidtext']]['nilai']==$val['nilai']){
							$datanilai[$i][$val['judul']]="<option selected value='".$val['nilai']."'>".$val['text']." (".$val['nilai'].")</option>";
						}else{
							$datanilai[$i][$val['judul']]="<option value='".$val['nilai']."'>".$val['text']." (".$val['nilai'].")</option>";
						}
					}
					
				}

			}

			// echo "<pre>";
			// print_r($datakeytotal);
			// echo "</pre>";
			foreach ($dataurut as $key => $val) {
				if(!isset($dataedit[$datadata[$key]['noidtext']]['nilai'])){
					$dataedit[$datadata[$key]['noidtext']]['nilai']=0;
				}

				if(!isset($dataedit[$datadata[$key]['noidtext']]['hasil'])){
					$dataedit[$datadata[$key]['noidtext']]['hasil']=0;
				}

				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$val;
					$tab.="<input id=noidtext".$key." hidden value='".$datadata[$key]['noidtext']."'>";
					if(isset($datakeytotal[$key])){
						$tab.="<input id=dataonchange".$key." hidden value=".$datakeytotal[$key].">";	
					}
				if($datadata[$key]['tipe']==0 or $datadata[$key]['tipe']==1 or $datadata[$key]['tipe']==3){
					$tab.="<input id=tipe".$key." hidden value='".$datadata[$key]['tipe']."'>";
					$tab.="<input id=nouruttotal".$key." hidden value='".$datadata[$key]['nouruttotal']."'>";
					$tab.="<input id=totaloperator".$key." hidden value='".$datadata[$key]['totaloperator']."'>";
					$tab.="<input id=kodetotaloperator".$key." hidden value='".$datadata[$key]['kodetotaloperator']."'>";
				}
				$tab.="</td>";
				if($datadata[$key]['tipenilai']!=''){
						if($datadata[$key]['tipenilai']=='0'){
							$tab.="<td align=center><input id=nilai".$key." class=myinputtextnumber style=width:55px; value='".$dataedit[$datadata[$key]['noidtext']]['nilai']."' onchange=\"kalkulasinilai1('".$key."','".$jlhx."')\">";
						}else{
							$tab.="<td align=center><select id=nilai".$key." style='width:99%;' onchange=\"kalkulasinilai1('".$key."','".$jlhx."')\">".$datanilai[$key][$datadata[$key]['kodetipenilai']]."</select>";
							
						}
					$tab.="<input id=tipenilai".$key." hidden value='".$datadata[$key]['tipenilai']."'>
					<input id=kodetipenilai".$key." hidden value='".$datadata[$key]['kodetipenilai']."'>";
					$tab.="</td>";
				}else{
					$tab.="<td align=center></td>";
				}
				if($datadata[$key]['bobot']!=''){
					$bobotan=doubleval($datadata[$key]['bobot']);
					$tab.="<td align=center><input id=bobot".$key." disabled class=myinputtextnumber style=width:55px; value='".$bobotan."'></td>";
					
				}else{
					$tab.="<td align=center></td>";
				}
				if(isset($datakeytotal[$key])){
					$tab.="<td align=center><input id=hasil".$key."   disabled  class=myinputtextnumber style=width:55px; value='".$dataedit[$datadata[$key]['noidtext']]['hasil']."' onchange=\"kalkulasitotal(".$datakeytotal[$key].")\">";
					$tab.="</td>";
				}else{
					$tab.="<td align=center><input id=hasil".$key."   disabled  class=myinputtextnumber style=width:55px; value='".$dataedit[$datadata[$key]['noidtext']]['hasil']."'></td>";
				}
							
				$tab.="</tr>";
				
			}
			$tab.="<tr class=rowcontent>
                    <td colspan=4>
                    <input type=hidden id=methodaddnew value=saveaddnew>
						<button onclick=saveaddnew('".$jlhx."'); style='width:500px;height:30px' class=mybutton>Save</button>
                    </td></tr>";
			
			$tab.="</tbody></table>";
			
			
		echo $tab;
	break;
	case 'addnew':
		if($param['jenis']=='editsave'){
			$str = "select * from ".$dbname.".sdm_kuudt1 where idht='".$param['idht']."' and idkpi ='".$param['idkpi']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$kpi=$bar['textkpi'];
				$bobot=$bar['bobot'];
				$porsisendiri=$bar['porsisendiri'];
				$porsiatasan=$bar['porsiatasan'];
			}
			$jenis='edit';
		}else{
			$jenis='new';
		}
		
		$place="placeholder='Formating:\nHurup tebal (bold) = <b>isi tulisan disini</b>\nHurup miring (italic) = <i>isi tulisan disini</i>\nGaris bawah (underline) = <u>isi tulisan disini</u>\n\natau gunakan = <font style=font-weight:bold;color:red;>isi tulisan disini</font>\nUntuk panduan lengkap cari di google = penulisan html atau tag html'";
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<input id=idkpi type=hidden value=".$param['idkpi'].">
					<td style=vertical-align:top>".$_SESSION['lang']['kpi']."</td>
					<td colspan=5><textarea ".$place." class=myinputtext style='width:495px;height:150px;font-size:14px;' id=kpinew >".$kpi."</textarea></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['bobot']."</td>
					<td><input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=bobotnew  value=".$bobot."></td>
					
					<td>Porsi Atasan</td>
					<td><input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=porsiatasannew  value=".$porsiatasan."></td>
					
					<td>Porsi Sendiri</td>
					<td><input class=myinputtextnumber style='width:100px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=porsisendirinew value=".$porsisendiri."></td>
				</tr>
                <tr>
                    <td><input type=hidden id=methodaddnew value=saveaddnew>
						</td>
                    <td colspan=40>
						<button onclick=saveaddnew('".$jenis."'); style='width:500px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	
	case 'saveaddnew':
		try {
			$owlPDO->beginTransaction();
			
			$noid=$param['thnnilai']."/".$param['unit']."/".$param['karyawanid'];
			$str = "delete from ".$dbname.".sdm_disiplindt WHERE noidht='".$noid."'";
			$owlPDO->exec($str);

			$datainsert="";
			for ($i=1; $i <=$param['jumlah']; $i++) { 
				if($param['noidtext'.$i]!=''){
					if($datainsert==''){
	        			$datainsert=" ('".$noid."','".$param['noidtext'.$i]."','".$param['nilai'.$i]."','".$param['hasil'.$i]."')";
	        		}else{
	        			$datainsert.=", ('".$noid."','".$param['noidtext'.$i]."','".$param['nilai'.$i]."','".$param['hasil'.$i]."')";
	        		}
				}
			}
			
			if($datainsert!=''){
				$query = "insert into ".$dbname.".sdm_disiplindt (noidht,noidtext,nilai,hasil) values ".$datainsert;
				echo $query;
				$owlPDO->exec($query);
			}
			
			
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'deldt':
		$where = " and idht='".$param['idht']."' and idkpi = '".$param['idkpi']."'";
		$str = "delete from ".$dbname.".sdm_kuudt1 WHERE 1=1 ".$where."";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'hapus':
		$where = " and noid='".$param['noid']."'";
		$str = "delete from ".$dbname.".sdm_disiplin WHERE 1=1 ".$where."";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'loaddata':
		$where = "";
		
		if ($thnnilai != ''){
			$where .= " AND tahun='".$thnnilai."'";
		}
		
		if ($param['unit'] != '') {
			$where .= " AND unit ='".$param['unit']."'";
		}

		if ($param['status'] != '') {
			$where .= " AND status='".$param['post']."'";
		}
					
		
		
		$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$tab="<br><table border=0 cellspacing=1 cellpadding=7 class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>".$_SESSION['lang']['nourut']."</th>
							<th align=center>".$_SESSION['lang']['tahun']."</th>
							<th align=center>".$_SESSION['lang']['unit']."</th>
							<th align=center>".$_SESSION['lang']['tanggal']."</th>
							<th align=center>".$_SESSION['lang']['karyawan']."</th>
							<th align=center>".$_SESSION['lang']['hasil']."</th>
							<th align=center>".$_SESSION['lang']['updateby']."</th>
							<th align=center>".$_SESSION['lang']['updatetime']."</th>
							<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
						</tr>
					</thead>
					<tbody>";

        $limit = 10;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 20;


		$tipejenis=array();
		$str = "SELECT * FROM ".$dbname.".sdm_5disiplin"; 
        $res = fetchdata($str);
        foreach($res as $bar){
            $tipejenis[$bar['id']] = $bar['tipe'];
        }

		$str = "SELECT COUNT(*) as jmlhrow FROM ".$dbname.".sdm_disiplin WHERE 1=1 ".$where; 
        $res = fetchdata($str);
        foreach($res as $bar){
            $jlhbrs = $bar['jmlhrow'];
        }
        
        $jumlahnilai=array();
        $totalhasil=array();
		$str = "select * from ".$dbname.".sdm_disiplindt ";
		$req = fetchdata($str);
		foreach($req as $val){
			if(!isset($jumlahnilai[$val['noidht']])){
				$jumlahnilai[$val['noidht']]=0;
			}
			$jumlahnilai[$val['noidht']]+=$val['nilai'];

			if($tipejenis[$val['noidtext']]==0){
				$totalhasil[$val['noidht']]+=$val['hasil'];
			}
		}
		
		
		$arrstatus=array('0'=>'Belum Diposting','1'=>'Posted');
		
		$str = "SELECT * FROM ".$dbname.".sdm_disiplin
				WHERE 1=1 ".$where."
				ORDER BY status asc, tahun DESC
				LIMIT ".$offset.",".$limit;
		$res = fetchdata($str);

        $no = $offset+1;
		foreach($res as $key=>$val){
			$color="style=text-align:center";
			if($jumlahnilai[$val['noid']]=='0' or !isset($jumlahnilai[$val['noid']])){
				$color="style=background-color:red;text-align:center; title='Belum Ada Inputan Nilai'";
			}
			$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center>".$val['tahun']."</td>
					<td>".getNamaOrg($val['unit'])."</td>
					<td align=center>".tanggalnormal($val['tanggal'])."</td>
					<td align=center>".getNamaKaryawan($val['karyawanid'])."</td>
					<td align=center ".$color.">".number_format($totalhasil[$val['noid']],2)."</td>
					<td align=center>".getNamaKaryawan($val['updateby'])."</td>
					<td align=center>".tanggalnormald($val['updatetime'])."</td>";
					
					// Approval Ditolak atau Reconfirm
					if ($val['statuspersetujuan'] == '0' || $val['statuspersetujuan'] == '3') {
						$tab.="<td align=center>
										<img src=images/application/application_edit.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"fillField('".$val['noid']."','".tanggalnormal($val['tanggal'])."','".$val['unit']."','".$val['tahun']."','".$val['karyawanid']."','".getNamaKaryawan($val['karyawanid'])."');\">
									</td>";
						$tab.="<td align=center>
										<img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedata('".$val['noid']."');\">
									</td>";
						$tab.="<td align=center>
										<img src=images/skyblue/submit.jpg class=zImgBtn title='Ajukan' caption='Ajukan' onclick=\"formajukan('".$val['noid']."');\">
									</td>";
					}elseif($val['statuspersetujuan'] == '9'){/* pengajuan */
						$tab.="<td align=center colspan='3' style='color: blue;'><b>".$stsapprv[$val['statuspersetujuan']]."</b></td>";
					}elseif($val['statuspersetujuan'] == '2'){/* ditolak */
						$tab.="<td align=center colspan='3' style='color: red;'><b>".$stsapprv[$val['statuspersetujuan']]."</b></td>";
					}elseif($val['statuspersetujuan'] == '1'){/* diterima */
						$tab.="<td align=center colspan='3' style='color: green;'><b>".$stsapprv[$val['statuspersetujuan']]."</b></td>";
					}
					
					$tab.="<td align=center>
									<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdf('".$val['noid']."');\">
								</td>
								<td align=center>
									<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detail('".$val['noid']."');\">
								</td>
							</tr>";
            $no += 1;
		}
		
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		

		$tab .= "</tbody></table>";

		echo $tab;
	break;
	case 'formajukan':
		/* Ambil dari transaksi */
		$sKar = selectQuery($dbname,"sdm_disiplin","karyawanid", "noid = '".$noid."'");
		$qKar = fetchData($sKar);
		$karyawanid = $qKar[0]['karyawanid'];

		$sDat = selectQuery($dbname,"datakaryawan","bagian,kodegolongan,lokasitugas", "karyawanid = '".$karyawanid."'");
		$qDat = fetchData($sDat);
		
		$karDepar = $qDat[0]['bagian'];
		$karGol = $qDat[0]['kodegolongan'];
		$karTugas = $qDat[0]['lokasitugas'];

		/* Commment aja kalau ga butuh lokasi tugas dari pengajuan */
		$lokTugasPengaju = " AND kodeunit = '".$karTugas."'";

		/* Approval Dinamis */
		$where = '';

		/* Cek Perdepartemen */
		$sStr = selectQuery($dbname,"setup_approval","COUNT(departemen) AS perdepartemen", "jenispersetujuan='".$jenispersetujuan."' AND departemen='".$karDepar."' ".$lokTugasPengaju."");
		$qStr = fetchData($sStr);
		$perdepartemen = $qStr[0]['perdepartemen'];
		$where .= " AND departemen = '".($perdepartemen > 0 ? $karDepar : '')."'";

		/* Cek Pergolongan */
		$sStr = selectQuery($dbname,"setup_approval","COUNT(golongan) AS pergolongan", "jenispersetujuan='".$jenispersetujuan."' AND golongan='".$karGol."' ".$lokTugasPengaju."");
		$qStr = fetchData($sStr);
		$pergolongan = $qStr[0]['pergolongan'];
		$where .= " AND golongan = '".($pergolongan > 0 ? $karGol : '')."'";

		/* Cek Per Username */
		$sStr = selectQuery($dbname,"setup_approval","COUNT(username) AS perusername", "jenispersetujuan='".$jenispersetujuan."' AND username='".$_SESSION['standard']['username']."' ".$lokTugasPengaju."");
		$qStr = fetchData($sStr);
		$perusername = $qStr[0]['perusername'];
		$where .= " AND username = '".($perusername > 0 ? $_SESSION['standard']['username'] : '')."'";

		// Setup Approval
		$sApp = selectQuery($dbname,"setup_approval","*", "jenispersetujuan = '".$jenispersetujuan."' ".$lokTugasPengaju." ".$where."", "level");
		$qApp = fetchData($sApp);

		/* Kasih warning apabila tidak ada yang cocok di setup */
		if (count($qApp) <= 0) {
				exit("warning : Silahkan tambahkan nama penyetuju melalui menu : Setup - Persetujuan");
		}
		
		// Input Data Approval
		$optApp = array();
		foreach ($qApp as $apv) {
				$optApp[$apv['level']][] = $apv['usernameapprv']; 
		}

		// Membuat Select Option
		$karid = makeOption($dbname, 'user', 'namauser,karyawanid');
		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$tab = '';
		$jlh = 0;
		foreach ($optApp as $level => $user) {
				/* Kode ini apabila menampilkan hanya 1 approval */
				if ($jlh > 0) {
					break;
				}
				/* akhir kode */

				$opt = '';
				foreach ($user as $username) {
						$opt .= "<option value='".$username."'>".$username."</option>";
				}
				if ($opt != '') {
						$jlh++;
						$tab .= "<tr class='rowcontent'>
												<td>Approval ke-".$level."</td>
												<td width='5px'>:</td>
												<td><select id='kepada".$level."' style='width: 99%';>".$opt."</select></td>
										</tr>";
				}
		}

		/* Ambil jumlah total approval */
		$tab .= "<input hidden id=jlh value='".$jlh."'>";
		/* Ambil no transaksi */
		$tab .= "<input hidden id=notransaksi_ajukan value='".$noid."'>";
		
		$tab .= "<tr>
								<td></td>
								<td></td>
								<td><button id='tomboldetail' class='mybutton' onclick=\"ajukan()\">".$_SESSION['lang']['diajukan']."</button></td>
						</tr>";
		echo $tab;
	break;
	case 'ajukan':
		/* cek apabila user membuka 2 tab */
		$sAppr = selectQuery($dbname,"sdm_disiplin","statuspersetujuan", "noid = '".$notransaksi."'");
		$qAppr = fetchData($sAppr);
		$stts = [1, 2, 9];
		if (in_array($qAppr[0]['statuspersetujuan'], $stts)) {
			exit("warning : Transaksi sudah diposting!");
		}

		/* Ambil dari lokasi tugas transaksi */
		$sKar = selectQuery($dbname,"sdm_disiplin","karyawanid", "noid = '".$notransaksi."'");
		$qKar = fetchData($sKar);
		$karyawanid = $qKar[0]['karyawanid'];

		$sDat = selectQuery($dbname,"datakaryawan","lokasitugas", "karyawanid = '".$karyawanid."'");
		$qDat = fetchData($sDat);
		$karTugas = $qDat[0]['lokasitugas'];
		
		/* Commment aja kalau ga butuh lokasi tugas dari pengajuan */
		$lokTugasPengaju = " AND kodeunit = '".$karTugas."'";
		
		/* Error jika Penyetuju tidak diinput */
		if ($jlh == 0) {
				exit("Warning : Isikan nama penyetuju");
		}
		
		/* Dapatkan Username Persetujuan */
		$appr = array();
		for ($lev = 1; $lev <= $jlh; $lev++) { 
				$appr[$lev] = checkPostGet("kepada".$lev."", '');/* Ambil per masing-masing user approval */
				$sApp = selectQuery($dbname,"setup_approval","*", "jenispersetujuan='".$jenispersetujuan."' AND level='".$lev."' ".$lokTugasPengaju."");
				$qApp = fetchData($sApp);

				if (count($qApp) > 0) {
						$tipeApp = $qApp[0]['tipe'];
						$departemenApp = $qApp[0]['departemen'];
						$tipekaryawanApp = $qApp[0]['tipekaryawan'];
						$jabatanApp = $qApp[0]['jabatan'];

						$data = array(
								'notransaksi'=> $notransaksi,
								'jenispersetujuan'=> $jenispersetujuan,
								'level'=> $lev,
								'status'=> '0',
						);
						
						if ($tipeApp == '1') {
								if ($departemenApp != '') {
										$sDep = selectQuery($dbname,"datakaryawan","*", "bagian = '".$departemenApp."'");
										$qDep = fetchData($sDep);

										foreach ($qDep as $kar) {
												$data['karyawanid'] = $kar['karyawanid'];
												$sIns = insertQuery($dbname,'approval',$data, array_keys($data));
												$owlPDO->exec($sIns);
										}
								}
								if ($tipekaryawanApp != '') {
										$sTKR = selectQuery($dbname,"datakaryawan","*", "bagian = '".$tipekaryawanApp."'");
										$qTKR = fetchData($sTKR);

										foreach ($qTKR as $kar) {
												$data['karyawanid'] = $kar['karyawanid'];
												$sIns = insertQuery($dbname,'approval',$data, array_keys($data));
												$owlPDO->exec($sIns);
										}
								}
								if ($jabatanApp != '') {
										$sJab = selectQuery($dbname,"datakaryawan","*", "bagian = '".$jabatanApp."'");
										$qJab = fetchData($sJab);

										foreach ($qJab as $kar) {
												$data['karyawanid'] = $kar['karyawanid'];
												$sIns = insertQuery($dbname,'approval',$data, array_keys($data));
												$owlPDO->exec($sIns);
										}
								}
						}else{
								if ($appr[$lev] != '') {
										$mokarid = makeOption($dbname,'user','namauser,karyawanid');
										$data['karyawanid'] = $mokarid[$appr[$lev]];
										$sIns = insertQuery($dbname,'approval',$data, array_keys($data));
										try { 
												$owlPDO->exec($sIns); 
										} catch (PDOException $e) {
												print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
										}
								}
						}
				}
		}

		/* Update Status persetujuan di transaksi */
		$data = array(
				'statuspersetujuan'=> 9,
		);
		$sUpt = updateQuery($dbname,'sdm_disiplin',$data, "noid = '".$notransaksi."'");
		try {
				$owlPDO->exec($sUpt); 
		} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;
	case 'posting':
			
			$str = "SELECT sum(nilai) as nilai FROM ".$dbname.".sdm_disiplindt WHERE noidht = '".$param['noid']."'"; 
			$res = fetchdata($str)[0];
			if($res['nilai']=='0'){
				exit("Warning: Belum ada penginputan nilai , proses dibatalkan.");
			}
			
			
			
			$data = array(
				'status'   => '1',
				'updatetime'=> date("Y-m-d H:i:s"),
				'updateby'  => $_SESSION['standard']['userid']
			);
			$where = "noid = '".$param['noid']."'";
			$query = updateQuery($dbname,'sdm_disiplin',$data,$where); //exit("error".$query);
			$owlPDO->exec($query);
	break;
	case 'reject':
		$data = array(
			'status'   => '2',
			'komentar'   => $param['komentar'],
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['idkpi']."' and jenispersetujuan='KUU' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		$data = array(
			'approval'   => '2',
			'posting'   => '0',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['idkpi']."'";
		$query = updateQuery($dbname,'sdm_kuu',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'approve':
		$str = "SELECT sum(bobot) as bobot FROM ".$dbname.".sdm_kuudt1 WHERE idht = ".$param['idkpi']; 
		$res = fetchdata($str)[0];
		if($res['bobot']!='100'){
			exit("Warning: Bobot harus 100%, proses dibatalkan.");
		}
		
		$data = array(
			'status'   => '1',
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['idkpi']."' and jenispersetujuan='KUU' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		$data = array(
			'approval'   => '1',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['idkpi']."'";
		$query = updateQuery($dbname,'sdm_kuu',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'unposting':
		$data = array(
			'status'   => '0',
			'updatetime'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "noid = '".$param['noid']."'";
		$query = updateQuery($dbname,'sdm_disiplin',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'fillField':
		$str = "SELECT a.id, a.jenis, a.karyawanid, a.dept, a.tahun, a.tanggal, a.kekuatan, a.kelemahan, b.idnilai, b.nilai
				FROM ".$dbname.".sdm_corevalueandmanmanagement a 
				JOIN ".$dbname.".sdm_corevalueandmanmanagement_dt b ON a.id=b.id
				WHERE a.id='".$id."'";
		$res = fetchdata($str);

		$fill = $res[0]['jenis']."###".$res[0]['karyawanid']."###".$res[0]['dept']."###".tanggalnormal($res[0]['tanggal'])."###".$res[0]['tahun']."###".$res[0]['kekuatan']."###".$res[0]['kelemahan'];
		foreach($res as $val){
			$fill .= "###".$val['idnilai'].$val['nilai'];
		}
		echo $fill;
	break;
	case'detail':
		
		
	
		$str = "SELECT * FROM ".$dbname.".sdm_disiplin WHERE noid = '".$param['noid']."'"; 
        $res = fetchdata($str)[0];
		$tahunnilai=$res['tahun'];
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=0 style='font-size:13px'>";
		}else{			
			$tab.="<table border=0>";
		}
	
		$tab.="<tr>
				<td>".$_SESSION['lang']['unit']."</td>
				<td>:</td>
				<td >".getNamaOrg($res['unit'])."</td>
				
				<td></td>

				<td>NIK</td>
				<td>:</td>
				<td >".getKary($res['karyawanid'],'nik')."</td>
				
				
			</tr><tr>
				<td>Tanggal</td>
				<td>:</td>
				<td >".tanggalnormal($res['tanggal'])."</td>
				
				<td></td>
				
				
				<td>Nama Karyawan</td>
				<td>:</td>
				<td >".getKary($res['karyawanid'],'namakaryawan')."</td>
				
			</tr>
			<tr>
				
				
				<td>".$_SESSION['lang']['tahun']."</td>
				<td>:</td>
				<td>".$res['tahun']."</td>
				
				<td></td>
				

				<td>Jabatan</td>
				<td>:</td>
				<td >".getJabatanKaryawan($res['karyawanid'])."</td>
				
			</tr>";
		$tab.="</table>";
		


		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=1 cellspacing=0 cellpadding=1 class=sortable style='font-size:13px'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		}
	
			
			
			$datadata=array();
			$dataurut=array();

			$str = "select * from ".$dbname.".sdm_5disiplin where tahun='".$tahunnilai."' order by nourut asc";
			$res = fetchdata($str);
			foreach($res as $val){
				$datadata[$val['nourut']]['noidtext']=$val['id'];
				$datadata[$val['nourut']]['tipe']=$val['tipe'];
				$datadata[$val['nourut']]['text']=$val['text'];
				$dataurut[$val['nourut']]=$val['text'];
				$datadata[$val['nourut']]['tipenilai']=$val['tipenilai'];
				$datadata[$val['nourut']]['kodetipenilai']=$val['kodetipenilai'];
				$datadata[$val['nourut']]['bobot']=$val['bobot'];
				$datadata[$val['nourut']]['nouruttotal']=$val['nouruttotal'];
				$datadata[$val['nourut']]['totaloperator']=$val['totaloperator'];
				$datadata[$val['nourut']]['operatornilai']=$val['operatornilai'];
				$datadata[$val['nourut']]['kodetotaloperator']=$val['kodetotaloperator'];
			}

			///print_r($datadata);

			$dataedit=array();
			$str = "select * from ".$dbname.".sdm_disiplindt where noidht='".$param['noid']."' order by nilai asc";
			$res = fetchdata($str);
			foreach($res as $val){
				$dataedit[$val['noidtext']]['nilai']=$val['nilai'];
				$dataedit[$val['noidtext']]['hasil']=$val['hasil'];

			}
			

			
			foreach ($dataurut as $key => $val) {
				if($datadata[$key]['tipe']==0){
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=left colspan=5><b>".$val." (".$datadata[$key]['bobot'].")</b></td>";
					$tab.="<td align=center>".$dataedit[$datadata[$key]['noidtext']]['hasil']."</td>";
				}elseif($datadata[$key]['tipe']==1){
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center></td>";
					$tab.="<td align=left colspan=4><b>".$val." (".$datadata[$key]['bobot'].")</b></td>";
					$tab.="<td align=center>".$dataedit[$datadata[$key]['noidtext']]['hasil']."</td>";
				}else{
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center>".$val;
					$tab.="</td>";
					if($datadata[$key]['tipenilai']!=''){
							if($datadata[$key]['tipenilai']=='0'){
								$tab.="<td align=center>".$dataedit[$datadata[$key]['noidtext']]['nilai']."";
							}else{
								$tab.="<td align=center>".$dataedit[$datadata[$key]['noidtext']]['nilai']."";
								
							}
						$tab.="</td>";
					}else{
						$tab.="<td align=center></td>";
					}
					if($datadata[$key]['bobot']!=''){
						$bobotan=doubleval($datadata[$key]['bobot']);
						$tab.="<td align=center>".$bobotan."</td>";
						
					}else{
						$tab.="<td align=center></td>";
					}
					$tab.="<td align=center>".$dataedit[$datadata[$key]['noidtext']]['hasil']."</td>";
					$tab.="</tr>";
				}
				
				
				
			}
			
			$tab.="</table>";
		
		if($param['tipeprint']=='pdf'){
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("kpi", array("Attachment" => false));
		}else{			
			echo $tab;
		}
	break;
}
