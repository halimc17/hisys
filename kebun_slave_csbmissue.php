<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses','');
$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;	
}
$path = "fileupload/pengajuantiket/";
$param['jumlah']=str_replace(",","",$param['jumlah']);
$nmjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

switch($method){
    case 'copy':
		$str = "SELECT * FROM " . $dbname . ".kebun_csbm_issues where divisi ='".$param['divisi']."' and periode ='".$param['dari']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str = "SELECT * FROM " . $dbname . ".kebun_csbm_issues where divisi ='".$param['divisi']."' and periode ='".$param['ke']."' and posting='1'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Warning : Data untuk periode ".$param['ke']." sudah diposting, proses dibatalkan.");
			}
			$str = "SELECT * FROM " . $dbname . ".kebun_csbm_issues where divisi ='".$param['divisi']."' and periode ='".$param['ke']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				echo "x";
			}
		}else{
			exit("Warning : Data sumber tidak ditemukan.");
		}
	break;
	case 'prosescopy':
		try {
		$owlPDO->beginTransaction();
			$str = "SELECT * FROM " . $dbname . ".kebun_csbm_issues where divisi ='".$param['divisi']."' and periode ='".$param['dari']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				$str = "SELECT * FROM " . $dbname . ".kebun_csbm_issues where divisi ='".$param['divisi']."' and periode ='".$param['ke']."'";
				$req = fetchdata($str);
				if(count($req)>0){
					$str = "delete from ".$dbname.".kebun_csbm_issues where divisi ='".$param['divisi']."' and periode ='".$param['ke']."'";
					$owlPDO->exec($str);
				}
				foreach ($res as $bar){
					$data = array(
						'kodeorg'   => $bar['kodeorg'],
						'divisi'    => $bar['divisi'],
						'periode'   => $param['ke'],
						'blok'      => $bar['blok'],
						'id'        => $bar['id'],
						'nilai'     => $bar['nilai'],
						'updateby'  => $_SESSION['standard']['userid'],
						'lastupdate'=> date("Y-m-d H:i:s")
					);
					$cols = array();
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}
					$query = insertQuery($dbname,'kebun_csbm_issues',$data,$cols); #exit("error".$query);
					$owlPDO->exec($query);
				}
			}
		
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
    case 'previewdata':
		switch($param['jenis']){
			case 'issues':
				$str = "SELECT max(level) as level  FROM " . $dbname . ".kebun_5csbm_issues order by nomor";
				$res = fetchdata($str);
				foreach ($res as $bar){
					$col = $bar['level'];
				}

			
				$kolom=array();
				$str = "SELECT *  FROM " . $dbname . ".kebun_5csbm_issues order by nomor";
				$res = fetchdata($str);
				foreach ($res as $bar){
					$idhd[$bar['id']]=$bar['id'];
					$head[$bar['id']][$bar['level']]=$bar['nama'];
					if($bar['level']=='2'){				
						$kolom[$bar['induk']]+=1;
					}
					
					
					if($bar['level']=='3'){				
						$dhead[$bar['induk']].=$bar['nilai']." : ".$bar['nama']."<br>";
						$opt[$bar['induk']].="<option value=".$bar['nilai'].">".$bar['nama']."</option>";
						
						$s = "SELECT min(nilai) as min, max(nilai) as max FROM " . $dbname . ".kebun_5csbm_issues  where induk='".$bar['induk']."'";
						$r = fetchdata($s)[0];
				
						$isian[$bar['induk']]=$bar['nilai'];
						$min[$bar['induk']]=$r['min'];
						$max[$bar['induk']]=$r['max'];
						$title[$bar['induk']].=$bar['nilai']." : ".$bar['nama']."\n";
					}
				}
			
				$rowspan="";
				if($col>1){
					$rowspan="rowspan=".$col."";
				}
				$tab.="
						<table border=0 cellpadding=5 cellspacing=1 class=sortable>
						<thead><tr class=rowheader>
							<td ".$rowspan." align=center>No</td>
							<td ".$rowspan." align=center>Block Code</td>
							<td ".$rowspan." align=center>Planting Year</td>
							<td ".$rowspan." align=center>Planting Material</td>
							<td ".$rowspan." align=center>Hectares</td>
							<td ".$rowspan." align=center>Palm Density</td>
							<td ".$rowspan." align=center>SPH</td>";
							$colom=0;
							foreach($head as $id => $vlvl){
								foreach($vlvl as $lvl => $name){
									$colom=$kolom[$id];
									if($lvl=='1'){								
										$tab.="<td colspan=".$colom." align=center>".$name."</td>";
									}
								}						
							}
					$tab.="</tr><tr class=rowheader>";
							foreach($head as $id => $vlvl){
								foreach($vlvl as $lvl => $name){
									if($lvl=='2'){								
										$tab.="<td align=center>".$name."</td>";
									}
								}						
							}
					
					$tab.="</tr><tr class=rowheader>";
							foreach($head as $id => $vlvl){
								foreach($vlvl as $lvl => $name){
									if($lvl=='2'){								
										$tab.="<td align=left valign=top>".$dhead[$id]."</td>";
									}
								}						
							}
					
					$tab.="</tr>
						</thead>
						<tbody>";
						$posting=0;
						$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_issues where divisi = '".$param['divisi']."' and periode='".$param['periode']."' order by periode asc";
						$res = fetchdata($str);
						foreach ($res as $bar){
							if($bar['periode']==$param['periode']){
								$posting+=$bar['posting'];
								$isibl[$bar['blok']][$bar['id']]=$bar['nilai'];
							}elseif($bar['periode']==periodelalu($param['periode'])){
								#$isibl[$bar['blok']][$bar['id']]=$bar['nilai'];
							}
						}
						
						if($posting>0){
							exit("error : Data sudah diposting.");
						}
						// echo"<pre>";
						// print_r($opt);
						// echo"</pre>";
						
						
						$str = "SELECT *  FROM " . $dbname . ".setup_blok where kodeorg like '".$param['divisi']."%' order by tahuntanam asc, kodeorg asc";
						$res = fetchdata($str);
						$no=0;
						foreach ($res as $bar){
							$no++;
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=center>".$no."</td>";
							$tab.="<td align=left style=display:none id=blok_".$no.">".$bar['kodeorg']."</td>";
							$tab.="<td align=left>".getNamaOrg($bar['kodeorg'])."</td>";
							$tab.="<td align=center>".$bar['tahuntanam']."</td>";
							$tab.="<td align=left>".$bar['jenisbibit']."</td>";
							$tab.="<td align=right>".number_format($bar['luasareaproduktif'],2)."</td>";
							$tab.="<td align=right>".number_format($bar['jumlahpokok'])."</td>";
							$tab.="<td align=right>".number_format($bar['jumlahpokok']/$bar['luasareaproduktif'],2)."</td>";
							$col=0;
							foreach($head as $id => $vlvl){
								foreach($vlvl as $lvl => $name){
									if($lvl=='2'){
										$col++;
										$tab.="<td align=center>
												<input hidden name=namaid[] id=id_".$no."_".$col." value='".$id."'>
												<input hidden id=min_".$no."_".$col." value='".$min[$id]."'>
												<input hidden id=max_".$no."_".$col." value='".$max[$id]."'>
												<input class=myinputtextnumber style=width:70px; onkeypress=\"return angka_doang(event);\" onkeyup=ceknilai('min_".$no."_".$col."','max_".$no."_".$col."',this.id) title=\"".$title[$id]."\" id=nilai_".$no."_".$col." value='".$isibl[$bar['kodeorg']][$id]."'>
										</td>";
									}
								}						
							}
						}
						
						
						
						$tab.="</tr>";
						$tab.="
						</tbody>
					</table>
					<button class=mybutton onclick=simpandetail('".$no."','".$col."')>" . $_SESSION['lang']['save'] . "</button>
				
				<div style=clear:both></div>
				<div id=loaddatadetail></div>
				";
			break;
			case'pica':
				$posting=0;
				$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_pica where divisi = '".$param['divisi']."' and periode='".$param['periode']."' and posting='1'";
				$res = fetchdata($str);
				if(count($res)>0){
					exit("error : Data sudah diposting.");
				}
				
				$tab="<fieldset>
					<legend>PICA</legend><table>";
				$tab.="<tr>
						<td valign=top>Problem Identification</td>
						<td valign=top>:</td>
						<td valign=top colspan=7><textarea rows=8 type=text id=problem style=width:690px;></textarea></td>
						</tr>";
				$tab.="<tr>
						<td valign=top>Corrective Action<br>(at each problem)</td>
						<td valign=top>:</td>
						<td valign=top colspan=7><textarea rows=8 type=text id=corrective style=width:690px;></textarea></td>
						</tr>";
				$tab.="<tr>
						<td valign=top>Outcome</td>
						<td valign=top>:</td>
						<td valign=top colspan=7><textarea rows=2 type=text id=outcome style=width:690px;></textarea></td>
						</tr>";	
				$tab.="<tr>
						
						<td>Mile Stone</td>
						<td>:</td>
						<td><input type='text' readonly=readonly style='width:130px;' class='myinputtext' id='milestone' onmousemove='setCalendar(this.id)' onkeypress='return false';  />
						</td>
						
						<td>Related Dept Support</td>
						<td>:</td>
						<td><input id=deptsupport class=myinputtext style=width:200px;></td>
						
						<td>PIC</td>
						<td>:</td>
						<td align=right><input id=pic class=myinputtext style=width:185px;></td>
						
						</tr>";		
				$tab.="<tr>
						<td valign=top><input hidden id=methodpica value='simpanpica'></td>
						<td valign=top></td>
						<td valign=top colspan=7>
							<button class=mybutton onclick=simpanpica()>" . $_SESSION['lang']['save'] . "</button>
							<button class=mybutton onclick=batalpica()>" . $_SESSION['lang']['cancel'] . "</button>
							</td>
						</tr>";		
				$tab.="</table></fieldset>
				";
				
				$tab.=" <div style=clear:both></div>
						<div id=loaddatapica></div>
						<div id=idpica></div>";
				
				
			break;
		}
		echo $tab;
    break;
	case'loaddatapica':
		$tab.="<fieldset>
					<legend>List Data</legend>
						<table border=0 cellpadding=5 cellspacing=1 class=sortable>
						<thead><tr class=rowheader>
							<td align=center>No</td>
							<td align=center>Problem Identification</td>
							<td align=center>Corrective Action (at each problem)</td>
							<td align=center>Outcome</td>
							<td align=center>Mile Stone</td>
							<td align=center>Related Dept Support</td>
							<td align=center>PIC</td>
							<td align=center colspan=2>Action</td>
						</tr>";
						
					$tab.="
						</thead>
						<tbody>";
						
					$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_pica where divisi like '".$param['divisi']."%' and periode = '".$param['periode']."' order by id asc";
					$res = fetchdata($str);
					$no=0;
					foreach ($res as $bar){
						$no++;
						$tab.="<tr class=rowcontent>";
						$tab.="<td valign=top align=center>".$no."</td>";
						$tab.="<td valign=top align=left>".nl2br($bar['problem'])."</td>";
						$tab.="<td valign=top align=left>".nl2br($bar['corrective'])."</td>";
						$tab.="<td valign=top align=left>".nl2br($bar['outcome'])."</td>";
						$tab.="<td valign=top align=left>".$bar['milestone']."</td>";
						$tab.="<td valign=top align=left>".$bar['deptsupport']."</td>";
						$tab.="<td valign=top align=left>".$bar['pic']."</td>";
						$tab.="<td valign=top align=center><img src=images/application/application_edit.png class=zImgBtn title=Edit onclick=\"fieldfill('".$bar['id']."');\"></td>";
						
						$tab.="<td valign=top align=center><img src=images/application/application_delete.png class=zImgBtn title=Delete onclick=\"delpica('".$bar['id']."');\"></td>";
					}
					
					$tab.="</tr>";
					$tab.="
					</tbody>
				</table>
			</fieldset>
			";
		echo $tab;		
	break;
	case'fieldfill':
		$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_pica where id = '".$param['id']."'";
		$res = fetchdata($str);
		foreach ($res as $bar){
			echo $bar['problem']."##".$bar['corrective']."##".$bar['outcome']."##".tanggalnormal($bar['milestone'])."##".$bar['deptsupport']."##".$bar['pic'];
		}
	break;
	case'simpandetail':
	try {
		$owlPDO->beginTransaction();
			$str="delete from ".$dbname.".kebun_csbm_issues where id='".$param['id']."' and periode='".$param['periode']."' and blok='".$param['blok']."'";
			$owlPDO->exec($str);
		
			$data = array(
				'kodeorg'   => $param['kodeorg'],
				'divisi'    => $param['divisi'],
				'periode'   => $param['periode'],
				'blok'      => $param['blok'],
				'id'        => $param['id'],
				'nilai'     => $param['nilai'],
				'updateby'  => $_SESSION['standard']['userid'],
				'lastupdate'=> date("Y-m-d H:i:s")
			);
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$query = insertQuery($dbname,'kebun_csbm_issues',$data,$cols);#exit("error".$query);
			$owlPDO->exec($query);
			
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'simpanpica':
	try {
		$owlPDO->beginTransaction();
			$data = array(
				'kodeorg'    => $param['kodeorg'],
				'divisi'     => $param['divisi'],
				'periode'    => $param['periode'],
				'problem'    => $param['problem'],
				'corrective' => $param['corrective'],
				'outcome'    => $param['outcome'],
				'milestone'  => tanggalsystemn($param['milestone']),
				'deptsupport'=> $param['deptsupport'],
				'pic'        => $param['pic'],
				'updateby'   => $_SESSION['standard']['userid'],
				'lastupdate' => date("Y-m-d H:i:s")
			);
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$query = insertQuery($dbname,'kebun_csbm_pica',$data,$cols);#exit("error".$query);
			$owlPDO->exec($query);
			
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'updatepica':
	try {
		$owlPDO->beginTransaction();
			$data = array(
				'kodeorg'    => $param['kodeorg'],
				'divisi'     => $param['divisi'],
				'periode'    => $param['periode'],
				'problem'    => $param['problem'],
				'corrective' => $param['corrective'],
				'outcome'    => $param['outcome'],
				'milestone'  => tanggalsystemn($param['milestone']),
				'deptsupport'=> $param['deptsupport'],
				'pic'        => $param['pic'],
				'updateby'   => $_SESSION['standard']['userid'],
				'lastupdate' => date("Y-m-d H:i:s")
			);
		
			$where = "id='".$param['id']."'";
			$query = updateQuery($dbname,'kebun_csbm_pica',$data,$where);
			$owlPDO->exec($query);
			
			
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	case'delpica':
		try {
		$owlPDO->beginTransaction();
		
		$str="delete from ".$dbname.".kebun_csbm_pica where id='".$param['id']."'";
		$owlPDO->exec($str);
		
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	
	break;
	
	case'posting':
	try {
		$owlPDO->beginTransaction();
			$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_issues where kodeorg='".$param['kodeorg']."' and divisi = '".$param['divisi']."' and periode = '".$param['periode']."'";
			$res = fetchdata($str);
			if(count($res)==0){
				exit("Warning : Key Issues belum di input.");
			}
			
			$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_pica where kodeorg='".$param['kodeorg']."' and divisi = '".$param['divisi']."' and periode = '".$param['periode']."'";
			$res = fetchdata($str);
			if(count($res)==0){
				exit("Warning : PICA belum di input.");
			}
		
		
			$data = array(
				'posting'    => '1',
				'postingby'  => $_SESSION['standard']['userid'],
				'postingdate'=> date("Y-m-d H:i:s")
			);
		
			$where = "kodeorg='".$param['kodeorg']."' and divisi = '".$param['divisi']."' and periode = '".$param['periode']."'";
			$query = updateQuery($dbname,'kebun_csbm_pica',$data,$where);
			$owlPDO->exec($query);
			
			$query = updateQuery($dbname,'kebun_csbm_issues',$data,$where);
			$owlPDO->exec($query);
			
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	break;
	
	case'loaddata':
		$tab="";
		$where="";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$_SESSION['empl']['kodeorganisasi']."')";
		} else {
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		
		if($param['kodeorg']!=''){
			$where.=" and kodeorg like '%".$param['kodeorg']."%'";
		}
		if($param['divisi']!=''){
			$where.=" and divisi like '%".$param['divisi']."%'";
		}
		if($param['periode']!=''){
			$where.=" and periode like '%".$param['periode']."%'";
		}
		
		$str = "SELECT distinct divisi  FROM " . $dbname . ".kebun_csbm_pica where 1=1 ".$where." group by divisi,periode order by divisi asc,periode desc";
		$resx = fetchdata($str);
		
		$tab.="<table cellpadding=5 cellspacing=1 border=0 width=100% class=sortable id=mytable>
		<thead>
			<tr class=rowheader>
				<th align=center width=30px rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
				<th align=center rowspan=2>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['periode']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['jenis']."</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['updateby'] . "</th>
				<th align=center rowspan=2>" . $_SESSION['lang']['status'] . "</th>
				<th align=center colspan='5'>" . $_SESSION['lang']['action'] . "</th>
			</tr>	
			<tr class=rowheader>
				<th align=center>Key Issues</th>
				<th align=center>PICA</th>
				
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				
			</tr>	
		</thead>
		<tbody>";
		
		$str = "SELECT distinct divisi  FROM " . $dbname . ".kebun_csbm_issues where 1=1 ".$where." group by divisi,periode order by periode desc,divisi asc";
		$res = fetchdata($str);
		if((count($res)+count($resx))==0){
			// //$tab.="<tr class=rowcontent><td align=center colspan=13>Data tidak ditemukan.</td></tr>"; $footd="";
			// echo $tab . "####" . $footd;
			// exit();
		}
		$limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = intval($_POST['page']); if ($page < 0){$page = 0;}}

        $offset = floatval($page) * floatval($limit);
        $maxdisplay = (floatval($page) * floatval($limit));
        $no = 0;
        $no = $maxdisplay;
		
		$data=array();
		$str = "SELECT *  FROM ".$dbname.".kebun_csbm_pica where 1=1 ".$where." group by divisi,periode order by periode desc,divisi asc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$data[$bar['divisi']][$bar['periode']]=$bar['periode'];
			$lupdate[$bar['divisi']][$bar['periode']]=$bar['updateby'];
			$posting[$bar['divisi']][$bar['periode']]=$bar['posting'];
			$isi[$bar['divisi']][$bar['periode']]['pica']=$bar['periode'];
		}	
		
		$str = "SELECT *  FROM ".$dbname.".kebun_csbm_issues where 1=1 ".$where." group by divisi,periode order by periode desc,divisi asc limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach ($res as $bar){
			$data[$bar['divisi']][$bar['periode']]=$bar['periode'];
			$lupdate[$bar['divisi']][$bar['periode']]=$bar['updateby'];
			$posting[$bar['divisi']][$bar['periode']]=$bar['posting'];
			$isi[$bar['divisi']][$bar['periode']]['issues']=$bar['periode'];
		}
		$jlhbrs = count($data);
		
		$no=0;
		$arrpost=array('0'=>'Belum Posting','1'=>'Sudah Posting');
		foreach ($data as $divisi => $vprd){
			foreach ($vprd as $prd){
				$no++;
				$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$lupdate[$divisi][$prd]."'");
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".substr($divisi,0,4)."' or kodeorganisasi='".$divisi."'");
				
				
				$tab.="<tr class=rowcontent style=height:20px>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".substr($divisi,0,4)." - ".$nmorg[substr($divisi,0,4)]."</td>";
				$tab.="<td align=left>".$divisi." - ".$nmorg[$divisi]."</td>";
				$tab.="<td align=left>".$prd."</td>";
				if($isi[$divisi][$prd]['issues']!=''){
					$tab.="<td align=left>Sudah diinput</td>";					
				}else{
					$tab.="<td align=left style=color:red;>Belum diinput</td>";					
				}
				if($isi[$divisi][$prd]['pica']!=''){
					$tab.="<td align=left>Sudah diinput</td>";					
				}else{
					$tab.="<td align=left style=color:red;>Belum diinput</td>";					
				}
				$tab.="<td align=left>".$nmkar[$lupdate[$divisi][$prd]]."</td>";
				$tab.="<td align=left>".$arrpost[$posting[$divisi][$prd]]."</td>";
				
				if($posting[$divisi][$prd]=='0'){				
					$tab.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('".substr($divisi,0,4)."','".$divisi."','".$prd."');\" ></td>";
					$tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".substr($divisi,0,4)."','".$divisi."','".$prd."');\" ></td>";
					
					$tab.="<td align=center width=20px><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"posting('".substr($divisi,0,4)."','".$divisi."','".$prd."');\" ></td>";
				}else{
					$tab.="<td style=width:20px></td>";
					$tab.="<td style=width:20px></td>";
					$tab.="<td style=width:20px></td>";
				}
				
				$tab.="<td align=center style=width:20px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview' onclick=\"preview('".substr($divisi,0,4)."','".$divisi."','".$prd."','html');\" ></td>";
				$tab.="<td align=center style=width:20px><img src=images/skyblue/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel' onclick=\"detailExcel('".substr($divisi,0,4)."','".$divisi."','".$prd."','excel');\" ></td>";
				
			}			
		}
		
		
		$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {$totrows = 1;}
		$isiRow=$footd="";
        for ($er = 1; $er <= $totrows; $er++) {$sel = ($page == $er - 1) ? 'selected' : '';$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";}
        $footd.="</tr><tr><td colspan=13 align=center>";
        if ($page == '0') {$footd.="<button class=mybutton disabled=true>Prev</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";}
        $footd.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {$footd.="<button class=mybutton disabled=true>Next</button>";} else {$footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";}
        $footd.="</td></tr>";
		
		$tab.="</tbody>
		<tfoot id=foothata>
		</tfoot>
	</table>";
        echo $tab . "####" . $footd;
	break;
	case'del':
	try {
		$owlPDO->beginTransaction();
		
		$str="delete from ".$dbname.".kebun_csbm_issues where periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."' and divisi='".$param['divisi']."'"; #exit("error".$str);
		$owlPDO->exec($str);
		
		
		$str="delete from ".$dbname.".kebun_csbm_pica where periode='".$param['periode']."' and kodeorg='".$param['kodeorg']."' and divisi='".$param['divisi']."'";
		$owlPDO->exec($str);
		
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
	
	break;
	case'preview':
	$kolom=array();
	$str = "SELECT *  FROM " . $dbname . ".kebun_5csbm_issues order by nomor";
	$res = fetchdata($str);
	foreach ($res as $bar){
		if($bar['level']=='2'){				
			$kolom[$bar['induk']]+=1;
		}
		$idhd[$bar['id']]=$bar['id'];
		$head[$bar['id']][$bar['level']]=$bar['nama'];
	}
	
	$tab="";
	if($param['tipe']=='pdf'){				
		$fontsize="10px";
		$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
	}elseif($param['tipe']=='excel'){
		$style="cellpadding=5 cellspacing=0 border=1 class=sortable";
	}else{
		$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
	}
	$rowspan="rowspan=2";
	$tab.="
			<label>ISSUES</label>
				<table ".$style.">
				<thead><tr class=rowheader>
					<th ".$rowspan." align=center>No</th>
					<th ".$rowspan." align=center>Block Code</th>
					<th ".$rowspan." align=center>Planting Year</th>
					<th ".$rowspan." align=center>Planting Material</th>
					<th ".$rowspan." align=center>Hectares</th>
					<th ".$rowspan." align=center>Palm Density</th>
					<th ".$rowspan." align=center>SPH</th>";
					$colom=0;
					foreach($head as $id => $vlvl){
						foreach($vlvl as $lvl => $name){
							$colom=$kolom[$id];
							if($lvl=='1'){								
								$tab.="<th colspan=".$colom." align=center>".$name."</th>";
							}
						}						
					}
			$tab.="</tr><tr class=rowheader>";
					foreach($head as $id => $vlvl){
						foreach($vlvl as $lvl => $name){
							if($lvl=='2'){								
								$tab.="<th align=center>".$name."</th>";
							}
						}						
					}
			
			$tab.="</tr></thead><tbody>";
				$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_issues where divisi = '".$param['divisi']."' order by periode asc";
				$res = fetchdata($str);
				foreach ($res as $bar){
					if($bar['periode']==$param['periode']){
						$isibl[$bar['blok']][$bar['id']]=$bar['nilai'];
					}elseif($bar['periode']==periodelalu($param['periode'])){
						#$isibl[$bar['blok']][$bar['id']]=$bar['nilai'];
					}
				}
				
				$str = "SELECT *  FROM " . $dbname . ".setup_blok where kodeorg like '".$param['divisi']."%' order by tahuntanam asc, kodeorg asc";
				$res = fetchdata($str);
				foreach ($res as $bar){
					$tt[$bar['kodeorg']]=$bar['tahuntanam'];
					$pkk[$bar['kodeorg']]=$bar['jumlahpokok'];
					$ha[$bar['kodeorg']]=$bar['luasareaproduktif'];
					$bbt[$bar['kodeorg']]=$bar['jenisbibit'];
				}	
							
				$data=array();
				$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_issues where divisi like '".$param['divisi']."%' and periode like '".$param['periode']."%' order by blok asc";
				$res = fetchdata($str);
				foreach ($res as $bar){
					$data[$bar['blok']]=$bar['blok'];
					$nilai[$bar['blok']][$bar['id']]=$bar['nilai'];
				}
				
				
				$no=0;
				foreach ($data as $blok){
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td align=left>".getNamaOrg($blok)."</td>";
					$tab.="<td align=center>".$tt[$blok]."</td>";
					$tab.="<td align=left>".$bbt[$blok]."</td>";
					$tab.="<td align=right>".number_format($ha[$blok],2)."</td>";
					$tab.="<td align=right>".number_format($pkk[$blok])."</td>";
					$tab.="<td align=right>".number_format($pkk[$blok]/$ha[$blok],2)."</td>";
					foreach($head as $id => $vlvl){
						foreach($vlvl as $lvl => $name){
							if($lvl=='2'){
								$nmisi=makeOption($dbname,'kebun_5csbm_issues','nilai,nama',"induk='".$id."' and nilai='".$nilai[$blok][$id]."'");
								$tab.="<td align=center>".$nmisi[$nilai[$blok][$id]]."</td>";
							}
						}						
					}
				}
				
				
				
				$tab.="</tr>";
				$tab.="
				</tbody></table>
				";
				
				
		
		if($param['tipe']!='pdf'){			
			//$tab.="</fieldset>";
		}

		$tab2.="<div style=clear:both></div><br>
					<label>PICA</label>
						<table ".$style.">
						<thead><tr class=rowheader>
							<th align=center>No</th>
							<th align=center>Problem Identification</th>
							<th align=center>Corrective Action (at each problem)</th>
							<th align=center>Outcome</th>
							<th align=center>Mile Stone</th>
							<th align=center>Related Dept Support</th>
							<th align=center>PIC</th>
						</tr>";
						
					$tab2.="
						</thead>
						<tbody>";
						
					$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_pica where divisi like '".$param['divisi']."%' and periode = '".$param['periode']."' order by id asc";
					$res = fetchdata($str);
					$no=0;
					foreach ($res as $bar){
						$no++;
						$tab2.="<tr class=rowcontent>";
						$tab2.="<td valign=top align=center>".$no."</td>";
						$tab2.="<td valign=top align=left>".nl2br($bar['problem'])."</td>";
						$tab2.="<td valign=top align=left>".nl2br($bar['corrective'])."</td>";
						$tab2.="<td valign=top align=left>".nl2br($bar['outcome'])."</td>";
						$tab2.="<td valign=top align=left>".$bar['milestone']."</td>";
						$tab2.="<td valign=top align=left>".$bar['deptsupport']."</td>";
						$tab2.="<td valign=top align=left>".$bar['pic']."</td>";
					}
					
					$tab2.="</tr>";
					$tab2.="
					</tbody>
				</table>
			
			";
			
		if($param['tipe']=='pdf'){		
			$dompdf = new Dompdf();
			if($param['jenis']=='pica'){
				$dompdf->load_html($tab2);
			}else{				
				$dompdf->load_html($tab.$tab2);
			}
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
			$dompdf->stream(str_replace("/","",$param['notransaksi']),array("Attachment"=>0));
		}elseif($param['tipe']=='html'){
			if($param['jenis']=='pica'){
				echo $tab2;				
			}else{				
				echo $tab.$tab2;				
			}
		}else{
			$nop = "csbm.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			if($param['jenis']=='pica'){
				$xls->addSheet('pica', $tab2);
			}else{				
				$xls->addSheet('issues', $tab);
				$xls->addSheet('pica', $tab2);
			}
			$xls->headers($nop);
			echo $xls->buildFile();
		}
		
	break;
	
}
?>