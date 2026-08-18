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
$jab      = getPostingJabatan('kpi');
$jlh = checkPostGet('jlh', '');
$jenispersetujuan = 'KPI';
$notransaksi = checkPostGet('notransaksi', '');

$arrnilai['1']='Januari';
$arrnilai['2']='Febuari';
$arrnilai['3']='Maret';
$arrnilai['4']='April';
$arrnilai['5']='Mei';
$arrnilai['6']='Juni';
$arrnilai['7']='Juli';
$arrnilai['8']='Agustus';
$arrnilai['9']='September';
$arrnilai['10']='Oktober';
$arrnilai['11']='November';
$arrnilai['12']='Desember';

$stsapprv = array('0'=> $_SESSION['lang']['wait_approval'],'1'=> $_SESSION['lang']['disetujui'],'2'=> $_SESSION['lang']['ditolak'],'3'=>'Dikoreksi','9'=>$_SESSION['lang']['pengajuan']);

switch ($method) {
	case 'getDept':
		$str = "SELECT * FROM ".$dbname.".datakaryawan WHERE karyawanid='".$nama."'";
		$res = fetchdata($str);

		echo $res[0]['bagian']."####".$res[0]['kodejabatan']."####".$res[0]['lokasitugas'];
	break;
	case 'getskore':
		$skor=0;
		$nilaiakhir=0;
		$nilaibobot=0;
		if($param['tipepenilaian']==0){
			$skor=doubleval($param['realisasi'])/doubleval($param['target']);
			$str = "SELECT * FROM ".$dbname.".sdm_5kpi WHERE id='".$param['id']."'";
			//echo $str;
			$res = fetchdata($str)[0];
			$nilaiakhir=$skor*($res['bobot']/100);
			//echo '$1$'.$res['bobot'];
			if($res['induk']!=''){
				$str2 = "SELECT * FROM ".$dbname.".sdm_5kpi WHERE id='".$res['induk']."'";
				//echo $str2;
				$res2 = fetchdata($str2)[0];
				$nilaiakhir=$nilaiakhir*($res2['bobot']/100);
			//echo '$2$'.$res2['bobot'];
				if($res2['induk']!=''){
					$str3 = "SELECT * FROM ".$dbname.".sdm_5kpi WHERE id='".$res2['induk']."'";
					//echo $str3;
					$res3 = fetchdata($str3)[0];
					$nilaiakhir=$nilaiakhir*($res3['bobot']/100);
			//echo '$3$'.$res3['bobot'];
				}
			}
		}else{
			$str = "SELECT * FROM ".$dbname.".sdm_5kpi WHERE id='".$param['id']."'";
			$res = fetchdata($str)[0];

			$strcx = "SELECT * FROM ".$dbname.".sdm_5setupscore WHERE judul='".$res['penilaian']."' and nilaidari<='".$param['realisasi']."' and  nilaisampai>='".$param['realisasi']."' and status='1'";
			// echo $strcx;
			// exit('error');
			$rescx = fetchdata($strcx)[0];
			if($rescx['nilai']==0 and $rescx['nilai']=='' and !isset($rescx['nilai'])){
				$rescx['nilai']=0;
			}
			$skor=doubleval($rescx['nilai']);
			$nilaiakhir=$skor*($res['bobot']/100);
			if($res['induk']!=''){
				$str2 = "SELECT * FROM ".$dbname.".sdm_5kpi WHERE id='".$res['id']."'";
				$res2 = fetchdata($str2)[0];
				$nilaiakhir=$nilaiakhir*($res2['bobot']/100);
				if($res2['induk']!=''){
					$str3 = "SELECT * FROM ".$dbname.".sdm_5kpi WHERE id='".$res2['id']."'";
					$res3 = fetchdata($str3)[0];
					$nilaiakhir=$nilaiakhir*($res3['bobot']/100);
				}
			}
		}
		
		echo $skor."####".$nilaiakhir;
		//exit('error');
	break;
	case 'insert':
		try {
            $owlPDO->beginTransaction();
			$str = "SELECT * FROM ".$dbname.".sdm_kpi WHERE karyawanid='".$param['karyawanid']."' and tahun='".$param['thnnilai']."' and penilaian='".$param['penilaian']."' ";
			$res = fetchdata($str);
			// if($param['penilaian']!='Q1'){
			// 	$penilaiansebelum = substr($param['penilaian'],0,1).(substr($param['penilaian'],1,1)-1);
				if(count($res)>0){
					// foreach($res as $bar){
					// 	if($penilaiansebelum==$bar['penilaian']){
					// 	}else{
							throw new PDOException("Penilaian ".$param['thnnilai']." Sudah ada.");						
						//}
					//}
				}
				// else{
				// 	throw new PDOException("Penilaian ".$penilaiansebelum." belum ada.");											
				// }
			//}
			
			
			
			$str = "SELECT * FROM ".$dbname.".sdm_kpi WHERE karyawanid='".$param['karyawanid']."' and penilaian='".$param['penilaian']."' and tahun='".$param['thnnilai']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Data sudah ada, dan dibuat oleh ".getNamaKaryawan($res[0]['createdby']).".");
			}
			
            $data = array(
				'karyawanid'   =>$param['karyawanid'],
				'jabatan'      =>$param['jabatan'],
				'dept'         =>$param['dept'],
				'manmanagement'=>$param['manmanagement'],
				'penilaian'    =>$param['penilaian'],
				'tahun'        =>$param['thnnilai'],
				'periodedr'    =>$param['bulandr'],
				'periodesd'    =>$param['bulansd'],
				'tanggal'      =>tanggalsystemn($param['tglnilai']),
				'createdby'    =>$_SESSION['standard']['userid'],
				'createdtime'  =>date('Y-m-d H:i:s'),
				'updateby'     =>$_SESSION['standard']['userid']
			);

           	$queryH = insertQuery($dbname,'sdm_kpi',$data,array_keys($data)); #exit("error".$queryH);
			$owlPDO->exec($queryH);
         
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warningcode : " . addslashes($e->getMessage());
        }
	break;
	case'loaddatadetail':
		$bobotxc=0;

		$tab="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
				<thead>
					<tr class=rowheader>
						<th align=center colspan='3'>".$_SESSION['lang']['kpi']."</th>
						<th align=center>".$_SESSION['lang']['bobot']."<br>(%)</th>
						<th align=center>Target</th>
						<th align=center>Realisasi</th>
						<th align=center>Skor</th>
						<th align=center>Nilai Akhir</th>
						<th align=center rowspan=3 >".$_SESSION['lang']['action']."</th>
					</tr>
				</thead>
				<tbody>";
			
			
			
			
			$str = "select * from ".$dbname.".sdm_kpi where karyawanid='".$param['karyawanid']."' and penilaian='".$param['penilaian']."' and tahun='".$param['thnnilai']."'";
			//echo $str;
			$res = fetchdata($str);
			foreach($res as $val){
				$idht = $val['id'];
			}

			$iddt1='';
			$strold = "select * from ".$dbname.".sdm_kpidt1 where idht='".$idht."' order by idkpi";
			$resold = fetchdata($strold);
			foreach($resold as $valold){
				if($iddt1==''){
					$iddt1="'".$valold['idkpi']."'";
				}else{
					$iddt1.=",'".$valold['idkpi']."'";
				}
			}

			$datanilai=array();
			if($iddt1!=''){
				$strold = "select * from ".$dbname.".sdm_kpidt2 where iddt1 in (".$iddt1.") order by iddt1";
				$resold = fetchdata($strold);
				foreach($resold as $valold){
					$datanilai[$valold['iddt1']]['skor']=$valold['skor'];
					$datanilai[$valold['iddt1']]['nilaiakhir']=$valold['nilaiakhir'];
					$datanilai[$valold['iddt1']]['realisasi']=$valold['realisasi'];
				}

			}
		
			$str = "select * from ".$dbname.".sdm_kpidt1 where idht='".$idht."' order by idkpi";
			//echo $str;
			$res = fetchdata($str);
			$detail=false;
			if(count($res)>0){
				$dataid='';
				$dataheader=array();
				$dataheader2=array();
				$str = "select a.*,b.jenis from ".$dbname.".sdm_kpidt1 a
				left join  ".$dbname.".sdm_5kpi b on a.idtextkpi=b.id 
				where a.idht='".$idht."' and b.jenis=0 order by idkpi";
				$res = fetchdata($str);			
				foreach($res as $val){
					$dataheader[$val['idtextkpi']]=$val['textkpi'];
					$dataheader2[$val['idtextkpi']]['bobot']=$val['bobot'];
					$dataheader2[$val['idtextkpi']]['idkpi']=$val['idkpi'];
				}
				

				if(count($dataheader)>0){
					$datasubheader=array();
					$datasubheader2=array();
					$str = "select a.*,b.jenis,b.induk from ".$dbname.".sdm_kpidt1 a
					left join ".$dbname.".sdm_5kpi b on a.idtextkpi=b.id  
					where a.idht='".$idht."' and b.jenis=1 order by idkpi";
					//echo $str;
					$res = fetchdata($str);			
					foreach($res as $val){
						$datasubheader[$val['induk']]=1;
						$datasubheader2[$val['induk']][$val['idtextkpi']]['id']=$val['idtextkpi'];
						$datasubheader2[$val['induk']][$val['idtextkpi']]['text']=$val['textkpi'];
						$datasubheader2[$val['induk']][$val['idtextkpi']]['bobot']=$val['bobot'];
						$datasubheader2[$val['induk']][$val['idtextkpi']]['idkpi']=$val['idkpi'];
					}
					

					$datatext=array();
					$datatext2=array();
					$str = "select a.*,b.jenis,b.induk,b.tipepenilaian from ".$dbname.".sdm_kpidt1 a
					left join ".$dbname.".sdm_5kpi  b on a.idtextkpi=b.id 
					where a.idht='".$idht."' and b.jenis=2 order by idkpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datatext[$val['induk']]=1;
						$datatext2[$val['induk']][$val['idtextkpi']]['id']=$val['idtextkpi'];
						$datatext2[$val['induk']][$val['idtextkpi']]['text']=$val['textkpi'];
						$datatext2[$val['induk']][$val['idtextkpi']]['bobot']=$val['bobot'];
						$datatext2[$val['induk']][$val['idtextkpi']]['target']=$val['target'];
						$datatext2[$val['induk']][$val['idtextkpi']]['idkpi']=$val['idkpi'];
						$datatext2[$val['induk']][$val['idtextkpi']]['tipepenilaian']=$val['tipepenilaian'];
					}
					

					foreach ($dataheader as $id => $text) {
						if($dataid==''){
							$dataid=$id;
						}else{
							$dataid.="###".$id;
						}
						if(isset($datasubheader[$id])){
							foreach ($datasubheader2[$id] as $valsub) {
								if($dataid==''){
									$dataid=$valsub['id'];
								}else{
									$dataid.="###".$valsub['id'];
								}
								if(isset($datatext2[$valsub['id']])){
									foreach ($datatext2[$valsub['id']] as $valtext) {
										if($dataid==''){
											$dataid=$valtext['id'];
										}else{
											$dataid.="###".$valtext['id'];
										}
									}
								}
							}
						}

						if(isset($datatext[$id])){
							foreach ($datatext2[$id] as $valtext) {
								if($dataid==''){
									$dataid=$valtext['id'];
								}else{
									$dataid.="###".$valtext['id'];
								}
							}
						}

					}
					$datacetak=array();
					$nox=0;
					foreach ($dataheader as $id => $text) {
						
					
						$nox++;
						$nox2=0;
						$bobotxc+=$dataheader2[$id]['bobot'];
						$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$id.">
							<td  colspan='3'>".$nox.".".nl2br($text)."<input id=kpi".$id." type=hidden value='".nl2br($text)."'></td>
							<td><input id=induk".$id." type=hidden value=''><input id=idkpi".$id." type=hidden value='".$dataheader2[$id]['idkpi']."'><input id=bobot".$id."   disabled  class=myinputtextnumber style=width:55px; value=".$dataheader2[$id]['bobot']."></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							";
						$tab.="<td></td>";	
							
						$tab.="</tr>";
						if(isset($datasubheader[$id])){
							foreach ($datasubheader2[$id] as $valsub) {
								$nox2++;
								$nox3=0;
								$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valsub['id'].">
									<td></td>
									<td colspan='2'>".$nox.".".$nox2.".".nl2br($valsub['text'])."<input id=kpi".$valsub['id']." type=hidden value='".nl2br($valsub['text'])."'></td>
									<td><input id=induk".$valsub['id']." type=hidden value='".$id."'><input id=idkpi".$valsub['id']." type=hidden value='".$valsub['idkpi']."'><input id=bobot".$valsub['id']."   disabled  class=myinputtextnumber style=width:55px; value=".$valsub['bobot']."></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									";
								$tab.="<td></td>";	
									
								$tab.="</tr>";
								if(isset($datatext2[$valsub['id']])){
									foreach ($datatext2[$valsub['id']] as $valtext) {
										$nox3++;
										if(!isset($datanilai[$valtext['idkpi']])){
											$datanilai[$valtext['idkpi']]['nilaiakhir']=0;
											$datanilai[$valtext['idkpi']]['skor']=0;
											$datanilai[$valtext['idkpi']]['realisasi']=0;
										}

										$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valtext['id'].">
											<td></td>
											<td></td>
											<td>".$nox.".".$nox2.".".$nox3.".".nl2br($valtext['text'])."<input id=kpi".$valtext['id']." type=hidden value='".nl2br($valtext['text'])."'></td>
											<td><input id=induk".$valtext['id']." type=hidden value='".$valsub['id']."'><input id=tipepenilaian".$valtext['id']." type=hidden value='".$valtext['tipepenilaian']."'><input id=idkpi".$valtext['id']." type=hidden value='".$valtext['idkpi']."'><input id=bobot".$valtext['id']."   disabled  class=myinputtextnumber style=width:55px; value=".$valtext['bobot']."></td>
											<td><input id=target".$valtext['id']." class=myinputtextnumber style=width:55px; disabled value='".($valtext['target'])."'></td>
											<td><input id=realisasi".$valtext['id']." class=myinputtextnumber style=width:55px;  onkeyup=getskore('".$valtext['id']."','".$dataid."'); value='".$datanilai[$valtext['idkpi']]['realisasi']."'></td>
											<td><input id=skor".$valtext['id']."  disabled class=myinputtextnumber style=width:55px;  value='".$datanilai[$valtext['idkpi']]['skor']."'></td>
											<td><input id=nilaiakhir".$valtext['id']."  disabled class=myinputtextnumber style=width:55px; value='".$datanilai[$valtext['idkpi']]['nilaiakhir']."'></td>
											";	
										
										
										$tab.="<td></td>";	
											
										$tab.="</tr>";
									}
								}
							}
						}

						if(isset($datatext[$id])){
							foreach ($datatext2[$id] as $valtext) {
								$nox2++;
										if(!isset($datanilai[$valtext['idkpi']])){
											$datanilai[$valtext['idkpi']]['nilaiakhir']=0;
											$datanilai[$valtext['idkpi']]['skor']=0;
											$datanilai[$valtext['idkpi']]['realisasi']=0;
										}
										$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valtext['id'].">
											<td></td>
											<td colspan='2'>".$nox.".".$nox2.".".nl2br($valtext['text'])."<input id=kpi".$valtext['id']." type=hidden value='".nl2br($valtext['text'])."'></td>
											<td><input id=induk".$valtext['id']." type=hidden value='".$id."'><input id=tipepenilaian".$valtext['id']." type=hidden value='".$valtext['tipepenilaian']."'><input id=idkpi".$valtext['id']." type=hidden value='".$valtext['idkpi']."'><input id=bobot".$valtext['id']."   disabled  class=myinputtextnumber style=width:55px; value=".$valtext['bobot']."></td>
											<td><input id=target".$valtext['id']." class=myinputtextnumber style=width:55px; disabled value='".($valtext['target'])."'></td>
											<td><input id=realisasi".$valtext['id']." class=myinputtextnumber style=width:55px;  onkeyup=getskore('".$valtext['id']."','".$dataid."'); value='".$datanilai[$valtext['idkpi']]['realisasi']."'></td>
											<td><input id=skor".$valtext['id']."  disabled class=myinputtextnumber style=width:55px;  value='".$datanilai[$valtext['idkpi']]['skor']."'></td>
											<td><input id=nilaiakhir".$valtext['id']."  disabled class=myinputtextnumber style=width:55px; value='".$datanilai[$valtext['idkpi']]['nilaiakhir']."'></td>
											";	
										
										$tab.="<td ></td>";	
											
										$tab.="</tr>";
							}
						}
					}
				}

				$xred='';

				if($bobotxc < 100 ){
					$xred='red';
				}

				$tab.="<tr class=rowcontent style=background-color:#ccfffd;font-weight:bold;>
				<td align=center colspan='3'>T O T A L</td>
				<td><input id=totaldt disabled class=myinputtextnumber style='width:55px;background-color: ".$xred.";' value=".$bobotxc."></td>
				<td></td>
				<td></td>
				<td></td>
				<td><input id=ttlnilaiakhir disabled class=myinputtextnumber style=width:55px;></td>
				<td style='text-align:center;width:25px;background-color:red;' id=tombolsave>
				<img src='images/save.png' class='resicon' title='Save' onclick=simpandtall('saveaddnew','edit','".$dataid."');></td>
				<input id=idht type=hidden value=".$idht."></tr>";
			}else{
				
				$dataid='';
				$dataheader=array();
				$dataheader2=array();
				$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='".$param['jabatan']."' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='".$param['karyawanid']."' and jenis='0' order by kpi";
				$res = fetchdata($str);			
				foreach($res as $val){
					$dataheader[$val['id']]=$val['kpi'];
					$dataheader2[$val['id']]=$val['bobot'];
				}
				$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='".$param['jabatan']."' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='' and jenis='0' order by kpi";
				$res = fetchdata($str);			
				foreach($res as $val){
					$dataheader[$val['id']]=$val['kpi'];
					$dataheader2[$val['id']]=$val['bobot'];
				}
				$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='' and jenis='0' order by kpi";
				$res = fetchdata($str);			
				foreach($res as $val){
					$dataheader[$val['id']]=$val['kpi'];
					$dataheader2[$val['id']]=$val['bobot'];
				}

				if(count($dataheader)>0){
					$datasubheader=array();
					$datasubheader2=array();
					$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='".$param['jabatan']."' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='".$param['karyawanid']."' and jenis='1' order by kpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datasubheader[$val['induk']]=1;
						$datasubheader2[$val['induk']][$val['id']]['id']=$val['id'];
						$datasubheader2[$val['induk']][$val['id']]['text']=$val['kpi'];
						$datasubheader2[$val['induk']][$val['id']]['bobot']=$val['bobot'];
					}
					$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='".$param['jabatan']."' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='' and jenis='1' order by kpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datasubheader[$val['induk']]=1;
						$datasubheader2[$val['induk']][$val['id']]['id']=$val['id'];
						$datasubheader2[$val['induk']][$val['id']]['text']=$val['kpi'];
						$datasubheader2[$val['induk']][$val['id']]['bobot']=$val['bobot'];
					}
					$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='' and jenis='1' order by kpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datasubheader[$val['induk']]=1;
						$datasubheader2[$val['induk']][$val['id']]['id']=$val['id'];
						$datasubheader2[$val['induk']][$val['id']]['text']=$val['kpi'];
						$datasubheader2[$val['induk']][$val['id']]['bobot']=$val['bobot'];
					}

					$datatext=array();
					$datatext2=array();
					$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='".$param['jabatan']."' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='".$param['karyawanid']."' and jenis='2' order by kpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datatext[$val['induk']]=1;
						$datatext2[$val['induk']][$val['id']]['id']=$val['id'];
						$datatext2[$val['induk']][$val['id']]['text']=$val['kpi'];
						$datatext2[$val['induk']][$val['id']]['bobot']=$val['bobot'];
						$datatext2[$val['induk']][$val['id']]['target']=$val['target'];
						$datatext2[$val['induk']][$val['id']]['tipepenilaian']=$val['tipepenilaian'];
					}
					$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='".$param['jabatan']."' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='' and jenis='2' order by kpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datatext[$val['induk']]=1;
						$datatext2[$val['induk']][$val['id']]['id']=$val['id'];
						$datatext2[$val['induk']][$val['id']]['text']=$val['kpi'];
						$datatext2[$val['induk']][$val['id']]['bobot']=$val['bobot'];
						$datatext2[$val['induk']][$val['id']]['target']=$val['target'];
						$datatext2[$val['induk']][$val['id']]['tipepenilaian']=$val['tipepenilaian'];
					}
					$str = "select * from ".$dbname.".sdm_5kpi where tahun='".$param['thnnilai']."' and jabatan='' and kodeorg='".getKary($param['karyawanid'],'lokasitugas')."' and karyawanid='' and jenis='2' order by kpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datatext[$val['induk']]=1;
						$datatext2[$val['induk']][$val['id']]['id']=$val['id'];
						$datatext2[$val['induk']][$val['id']]['text']=$val['kpi'];
						$datatext2[$val['induk']][$val['id']]['bobot']=$val['bobot'];
						$datatext2[$val['induk']][$val['id']]['target']=$val['target'];
						$datatext2[$val['induk']][$val['id']]['tipepenilaian']=$val['tipepenilaian'];
					}

					foreach ($dataheader as $id => $text) {
						if($dataid==''){
							$dataid=$id;
						}else{
							$dataid.="###".$id;
						}
						if(isset($datasubheader[$id])){
							foreach ($datasubheader2[$id] as $valsub) {
								if($dataid==''){
									$dataid=$valsub['id'];
								}else{
									$dataid.="###".$valsub['id'];
								}
								if(isset($datatext2[$valsub['id']])){
									foreach ($datatext2[$valsub['id']] as $valtext) {
										if($dataid==''){
											$dataid=$valtext['id'];
										}else{
											$dataid.="###".$valtext['id'];
										}
									}
								}
							}
						}

						if(isset($datatext[$id])){
							foreach ($datatext2[$id] as $valtext) {
								if($dataid==''){
									$dataid=$valtext['id'];
								}else{
									$dataid.="###".$valtext['id'];
								}
							}
						}

					}
					$datacetak=array();
					$nox=0;
					foreach ($dataheader as $id => $text) {
						
					
						$nox++;
						$nox2=0;
						$bobotxc+=$dataheader2[$id];
						$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$id.">
							<td  colspan='3'>".$nox.".".nl2br($text)."<input id=kpi".$id." type=hidden value='".nl2br($text)."'></td>
							<td><input id=induk".$id." type=hidden value=''><input id=idkpi".$id." type=hidden><input id=bobot".$id."   disabled  class=myinputtextnumber style=width:55px; value=".$dataheader2[$id]."></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							";
						$tab.="<td></td>";	
							
						$tab.="</tr>";
						if(isset($datasubheader[$id])){
							foreach ($datasubheader2[$id] as $valsub) {
								$nox2++;
								$nox3=0;
								$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valsub['id'].">
									<td></td>
									<td colspan='2'>".$nox.".".$nox2.".".nl2br($valsub['text'])."<input id=kpi".$valsub['id']." type=hidden value='".nl2br($valsub['text'])."'></td>
									<td><input id=induk".$valsub['id']." type=hidden value='".$id."'><input id=idkpi".$valsub['id']." type=hidden><input id=bobot".$valsub['id']."   disabled  class=myinputtextnumber style=width:55px; value=".$valsub['bobot']."></td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									";
								$tab.="<td></td>";	
									
								$tab.="</tr>";
								if(isset($datatext2[$valsub['id']])){
									foreach ($datatext2[$valsub['id']] as $valtext) {
										$nox3++;
										$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valtext['id'].">
											<td></td>
											<td></td>
											<td>".$nox.".".$nox2.".".$nox3.".".nl2br($valtext['text'])."<input id=kpi".$valtext['id']." type=hidden value='".nl2br($valtext['text'])."'></td>
											<td><input id=induk".$valtext['id']." type=hidden value='".$valsub['id']."'><input id=tipepenilaian".$valtext['id']." type=hidden value='".$valtext['tipepenilaian']."'><input id=idkpi".$valtext['id']." type=hidden><input id=bobot".$valtext['id']."   disabled  class=myinputtextnumber style=width:55px; value=".$valtext['bobot']."></td>
											<td><input id=target".$valtext['id']." class=myinputtextnumber style=width:55px; disabled value='".($valtext['target'])."'></td>
											<td><input id=realisasi".$valtext['id']." class=myinputtextnumber style=width:55px;  onkeyup=getskore('".$valtext['id']."','".$dataid."'); value='0'></td>
											<td><input id=skor".$valtext['id']."  disabled class=myinputtextnumber style=width:55px;  value='0'></td>
											<td><input id=nilaiakhir".$valtext['id']."  disabled class=myinputtextnumber style=width:55px; value='0'></td>
											";	
										
										
										$tab.="<td></td>";	
											
										$tab.="</tr>";
									}
								}
							}
						}

						if(isset($datatext[$id])){
							foreach ($datatext2[$id] as $valtext) {
								$nox2++;
										$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valtext['id'].">
											<td></td>
											<td colspan='2'>".$nox.".".$nox2.".".nl2br($valtext['text'])."<input id=kpi".$valtext['id']." type=hidden value='".nl2br($valtext['text'])."'></td>
											<td><input id=induk".$valtext['id']." type=hidden value='".$id."'><input id=tipepenilaian".$valtext['id']." type=hidden value='".$valtext['tipepenilaian']."'><input id=idkpi".$valtext['id']." type=hidden><input id=bobot".$valtext['id']."   disabled  class=myinputtextnumber style=width:55px; value=".$valtext['bobot']."></td>
											<td><input id=target".$valtext['id']." class=myinputtextnumber style=width:55px; disabled value='".($valtext['target'])."'></td>
											<td><input id=realisasi".$valtext['id']." class=myinputtextnumber style=width:55px;  onkeyup=getskore('".$valtext['id']."','".$dataid."'); value='0'></td>
											<td><input id=skor".$valtext['id']."  disabled class=myinputtextnumber style=width:55px;  value='0'></td>
											<td><input id=nilaiakhir".$valtext['id']."  disabled class=myinputtextnumber style=width:55px; value='0'></td>
											";	
										
										$tab.="<td ></td>";	
											
										$tab.="</tr>";
							}
						}
					}
				}
				
				$xred='';
				if($bobotxc<100 ){
					$xred='red';
				}
				$tab.="<tr class=rowcontent style=background-color:#ccfffd;font-weight:bold;>
				<td align=center colspan='3'>T O T A L</td>
				<td><input id=totaldt disabled class=myinputtextnumber style='width:55px;background-color: ".$xred.";' value=".$bobotxc."></td>
				<td></td>
				<td></td>
				<td></td>
				<td><input id=ttlnilaiakhir disabled class=myinputtextnumber style=width:55px;></td>
				<td style='text-align:center;width:25px;background-color:red;' id=tombolsave>
				<img src='images/save.png' class='resicon' title='Save' onclick=simpandtall('saveaddnew','new','".$dataid."');></td>
				<input id=idht type=hidden value=".$idht."></tr>";
			}
			
			$no++;

			
			
			
		echo $tab;
	break;
	case 'addnew':
		if($param['jenis']=='editsave'){
			$str = "select * from ".$dbname.".sdm_kpidt1 where idht='".$param['idht']."' and idkpi ='".$param['idkpi']."'";
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
			// if(($param['porsisendiri']+$param['porsiatasan'])!=100){
			// 	throw new PDOException("Jumlah persen porsi sendiri dan persen porsi atasan harus 100%.");
			// }
			
			// echo"<pre>";
			// print_r($param);
			// exit("error");
			
			if($param['idkpi']==''){
				$str = "select max(idkpi) as idkpi from ".$dbname.".sdm_kpidt1";
				$res = fetchdata($str);
				if(count($res)>0){
					$param['idkpi']=$res[0]['idkpi']+1;
				}else{
					$param['idkpi']=1;
				}
				
			}
			switch ($param['jenis']){
				case'new':
					
					$data = array(
						'idht'        => $param['idht'],
						'idkpi'       => $param['idkpi'],
						'idtextkpi'   => $param['idtextkpi'],
						'textkpi'     => $param['kpi'],
						'bobot'       => $param['bobot'],
						'target'	  => $param['target'],
						'createdby'   => $_SESSION['standard']['userid'],
						'createdtime' => date("Y-m-d H:i:s"),
						'updateby'    => $_SESSION['standard']['userid']
					);
					
					$cols = array();
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}

					$query = insertQuery($dbname,'sdm_kpidt1',$data,$cols);
					$owlPDO->exec($query);
				
					$data = array();
					$data = array(
						'iddt1'          => $param['idkpi'],
						'penilaian'      => '',
						'nilaiakhir'     => $param['nilaiakhir'],
						'skor' 			 => $param['skor'],
						'target'   		 => $param['target'],
						'realisasi'      => $param['realisasi'],
						'createdby'      => $_SESSION['standard']['userid'],
						'createdtime'    => date("Y-m-d H:i:s"),
						'updateby'       => $_SESSION['standard']['userid']
					);
					
					$cols = array();
					foreach($data as $key=>$row) {
						$cols[] = $key;
					}

					$query = insertQuery($dbname,'sdm_kpidt2',$data,$cols); 
					if(!empty($query) ){
						$owlPDO->exec($query);
					}
				break;
				case'edit':
					$data = array(
						'idtextkpi'   => $param['idtextkpi'],
						'textkpi'     => $param['kpi'],
						'bobot'       => $param['bobot'],
						'target'	  => $param['target'],
						'updateby'    => $_SESSION['standard']['userid']
					);
					$where = "idht='".$param['idht']."' and idkpi = '".$param['idkpi']."'";
					$query = updateQuery($dbname,'sdm_kpidt1',$data,$where); //exit("error".$query);
					$owlPDO->exec($query);
					
					
					#cek dulu pastikan sudah ada, jika belum ada insert dulu
					$where = "iddt1='".$param['idkpi']."' and penilaian=''";
					$str = "select * from ".$dbname.".sdm_kpidt2 where 1=1 and ".$where."";
					//exit("error".$str);
					$res = fetchdata($str);
					if(count($res)>0){						
						$data = array();
						$data = array(
							'nilaiakhir'     => $param['nilaiakhir'],
							'skor' 			 => $param['skor'],
							'target'   		 => $param['target'],
							'realisasi'      => $param['realisasi'],
							'updateby'       => $_SESSION['standard']['userid']
						);
						$query = updateQuery($dbname,'sdm_kpidt2',$data,$where); //exit("error".$query);
						$owlPDO->exec($query);
					}else{
						$data = array();
						$data = array(
							'iddt1'          => $param['idkpi'],
							'penilaian'      => '',
							'nilaiakhir'     => $param['nilaiakhir'],
							'skor' 			 => $param['skor'],
							'target'   		 => $param['target'],
							'realisasi'      => $param['realisasi'],
							'createdby'      => $_SESSION['standard']['userid'],
							'createdtime'    => date("Y-m-d H:i:s"),
							'updateby'       => $_SESSION['standard']['userid']
						);
						
						$cols = array();
						foreach($data as $key=>$row) {
							$cols[] = $key;
						}

						$query = insertQuery($dbname,'sdm_kpidt2',$data,$cols); 
						//echo $query;
						if(!empty($query) ){
							$owlPDO->exec($query);
						}
					}
				break;
			}
			
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'deldt':
		$where = " and idht='".$param['idht']."' and idkpi = '".$param['idkpi']."'";
		$str = "delete from ".$dbname.".sdm_kpidt1 WHERE 1=1 ".$where."";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'hapus':
		$where = " and id='".$param['id']."'";
		$str = "delete from ".$dbname.".sdm_kpi WHERE 1=1 ".$where."";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	break;
	case 'loaddata':
		$where = "";
		if ($nama != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where namakaryawan like '%".$nama."%')";
		}

		if ($dept != '') {
			$where .= " AND dept='".$dept."'";
		}

		if ($thnnilai != ''){
			$where .= " AND tahun='".$thnnilai."'";
		}
		
		if ($param['unit'] != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas like '%".trim($param['unit'])."%')";
		}
		if ($param['gol'] != '') {
			$where .= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where kodegolongan = '".trim($param['gol'])."')";
		}
		if ($param['penilaian'] != '') {
			$where .= " AND penilaian='".$param['penilaian']."'";
		}
		if ($param['post'] != '') {
			$where .= " AND posting='".$param['post']."'";
		}
		$where.= " AND karyawanid in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas in (".getOrgDetail(2)."))";
		
		$nmgol = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$tab="<br><table border=0 cellspacing=1 cellpadding=7 style='width:100%;' class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>".$_SESSION['lang']['nourut']."</th>
							<th align=center>".$_SESSION['lang']['tahun']."</th>
							<th align=center>".$_SESSION['lang']['penilaian']."</th>
							<th align=center>".$_SESSION['lang']['lokasitugas']."</th>
							<th align=center>".$_SESSION['lang']['nik2']."</th>
							<th align=center>".$_SESSION['lang']['namakaryawan']."</th>
							<th align=center>".$_SESSION['lang']['jabatan']."</th>
							<th align=center>".$_SESSION['lang']['kodegolongan']."</th>
							<th align=center>".$_SESSION['lang']['departemen']."</th>
							<th align=center>".$_SESSION['lang']['tanggal']."</th>
							<th align=center>".$_SESSION['lang']['status']."</th>
							<th align=center>".$_SESSION['lang']['bobot']."</th>
							<th align=center>".$_SESSION['lang']['total']."</th>
							<th align=center>".$_SESSION['lang']['createby']."</th>
							<th hidden align=center>".$_SESSION['lang']['updateby']."</th>
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

		$str = "SELECT COUNT(*) as jmlhrow FROM ".$dbname.".sdm_kpi WHERE 1=1 ".$where; 
        $res = fetchdata($str);
        foreach($res as $bar){
            $jlhbrs = $bar['jmlhrow'];
        }
        
		
		$str = "select a.*,b.jenis from ".$dbname.".sdm_kpidt1 a 
		left join ".$dbname.".sdm_5kpi b on a.idtextkpi=b.id 
		where a.idht in (SELECT id FROM ".$dbname.".sdm_kpi WHERE 1=1 ".$where.") and b.jenis in ('0','2')";
		$req = fetchdata($str);
		foreach($req as $val){
			if($val['jenis']!='0'){
				$sql = "select * from ".$dbname.".sdm_kpidt2 where iddt1 ='".$val['idkpi']."' order by iddt1";
				$req = fetchdata($sql);
				foreach($req as $bar){
					$totalproporsi[$val['idht']]+=$bar['nilaiakhir'];
				}
			}
			if($val['jenis']=='0'){
				$bobot[$val['idht']]+=$val['bobot'];
			}
		}

		
		
		$arrstatus=array('0'=>'Belum Diajukan','1'=>'Disetujui','2'=>'Ditolak','9'=>'Proses Persetujuan');
		
		$str = "SELECT * FROM ".$dbname.".sdm_kpi
				WHERE 1=1 ".$where."
				ORDER BY posting asc, id DESC
				LIMIT ".$offset.",".$limit;
		$res = fetchdata($str);

        $no = $offset+1;
		foreach($res as $key=>$val){
			$color="style=text-align:center";
			// if($bobot[$val['id']]!='100'){
			// 	$color="style=background-color:red;text-align:center; title='Bobot harus 100%'";
			// }
			$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center>".$val['tahun']."</td>
					<td align=center>".$arrnilai[$val['penilaian']]."</td>
					<td>".getNamaOrg(getKary($val['karyawanid'],'lokasitugas'))."</td>
					<td>".getKary($val['karyawanid'],'nik')."</td>
					<td>".getKary($val['karyawanid'],'namakaryawan')."</td>
					<td>".getNamaJabatan($val['jabatan'])."</td>
					<td align=center>".$nmgol[getKary($val['karyawanid'],'kodegolongan')]."</td>
					<td>".getNamaDept($val['dept'])."</td>
					<td align=center>".tanggalnormal($val['tanggal'])."</td>
					<td align=center style=color:blue;cursor:pointer; onclick=gethistoriapproval(".$val['id'].")>".$arrstatus[$val['approval']]."</td>
					<td ".$color.">".$bobot[$val['id']]."</td>
					<td align=center>".$totalproporsi[$val['id']]."</td>
					<td align=center style=font-size:10px;>".getNamaKaryawan($val['createdby'])."<br>".tanggalnormald($val['createdtime'])."</td>
					<td hidden align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['lastupdate'])."</td>";
					
					// Approval Ditolak atau Reconfirm
					// Approval Ditolak atau Reconfirm
					if ($val['approval'] == '0' || $val['approval'] == '3') {
						$tab.="<td align=center>
										<img src=images/application/application_edit.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"fillField('".$val['id']."','".$val['karyawanid']."','".$val['jabatan']."','".$val['dept']."','".$val['manmanagement']."','".$val['penilaian']."','".$val['tahun']."','".$val['periodedr']."','".$val['periodesd']."','".tanggalnormal($val['tanggal'])."','".getKary($val['karyawanid'],'lokasitugas')."');\">
									</td>";
						$tab.="<td align=center>
										<img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedata('".$val['id']."');\">
									</td>";
						$tab.="<td align=center>
										<img src=images/skyblue/submit.jpg class=zImgBtn title='Ajukan' caption='Ajukan' onclick=\"formposting('".$val['id']."');\">
									</td>";
					}elseif($val['approval'] == '9'){/* pengajuan */
						$tab.="<td align=center colspan=3 style='color: blue;'><b>".$stsapprv[$val['approval']]."</b></td>";
					}elseif($val['approval'] == '2'){/* ditolak */
						$tab.="<td align=center colspan=3 style='color: red;'><b>".$stsapprv[$val['approval']]."</b></td>";
					}elseif($val['approval'] == '1'){/* diterima */
						$tab.="<td align=center colspan=3 style='color: green;'><b>".$stsapprv[$val['approval']]."</b></td>";
					}


					$tab.="<td align=center>
									<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdf('".$val['id']."');\">
								</td>
								<td align=center>
									<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detail('".$val['id']."');\">
								</td>
							</tr>";
            $no += 1;
		}
		
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		

		$tab .= "</tbody></table>";

		echo $tab;
	break;
	case 'formposting':
		
		/* Ambil dari transaksi */
		$sKar = selectQuery($dbname,"sdm_kpi","karyawanid", "id = '".$param['idkpi']."'");
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
		$sStr = selectQuery($dbname,"setup_approval","COUNT(departemen) AS perdepartemen", "jenispersetujuan = '".$jenispersetujuan."' AND departemen = '".$karDepar."' ".$lokTugasPengaju."");
		$qStr = fetchData($sStr);
		$perdepartemen = $qStr[0]['perdepartemen'];
		$where .= " AND departemen = '".($perdepartemen > 0 ? $karDepar : '')."'";

		/* Cek Pergolongan */
		$sStr = selectQuery($dbname,"setup_approval","COUNT(golongan) AS pergolongan", "jenispersetujuan = '".$jenispersetujuan."' AND golongan = '".$karGol."' ".$lokTugasPengaju."");
		$qStr = fetchData($sStr);
		$pergolongan = $qStr[0]['pergolongan'];
		$where .= " AND golongan = '".($pergolongan > 0 ? $karGol : '')."'";

		// Setup Approval
		$sApp = selectQuery($dbname,"setup_approval","*", "jenispersetujuan = '".$jenispersetujuan."' AND kodeunit = '".$_SESSION['empl']['lokasitugas']."' and level='1' ".$where."", "level");
		$qApp = fetchData($sApp);

				/* Kasih warning apabila tidak ada yang cocok di setup */
		if (count($qApp) <= 0) {
			exit("warning : Silahkan tambahkan nama penyetuju melalui menu : Setup - Persetujuan");
		}

		// Input Data Approval
		$optApp = array();
		foreach ($qApp as $apv) {
			$optApp[$apv['level']][] = $apv['karyawanid']; 
		}

		// Membuat Select Option
		$karid = makeOption($dbname, 'user', 'namauser,karyawanid');
		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		$tab = '';
		$jlh = 0;
		foreach ($optApp as $level => $user) {
						/* Kode ini apabila menampilkan hanya 1 approval */
						// if ($jlh > 0) {
						// 	break;
						// }
						/* akhir kode */

			$opt = '';
			foreach ($user as $username) {
				$opt .= "<option value='".$username."'>".$nmkar[$username]."</option>";
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
		$tab .= "<input hidden id=notransaksi_ajukan value='".$param['idkpi']."'>";

		$tab .= "<tr>
					<td></td>
					<td></td>
					<td><button id='tomboldetail' class='mybutton' onclick=\"ajukan()\">".$_SESSION['lang']['diajukan']."</button></td>
				</tr>";
		echo $tab;
	break;
	case 'ajukan':
		$notransaksi = $param['notransaksi'];
		/* cek apabila user membuka 2 tab */
		$sAppr = selectQuery($dbname,"sdm_kpi","approval", "id = '".$notransaksi."'");
		$qAppr = fetchData($sAppr);
		$stts = [1, 2, 9];
		if (in_array($qAppr[0]['approval'], $stts)) {
			exit("warning : Transaksi sudah diposting!");
		}

		/* Ambil dari lokasi tugas transaksi */
		$sKar = selectQuery($dbname,"sdm_kpi","karyawanid", "id = '".$notransaksi."'");
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
						
						$data['karyawanid'] = $appr[$lev];
						$sIns = insertQuery($dbname,'approval',$data, array_keys($data));
						try { 
								$owlPDO->exec($sIns); 
						} catch (PDOException $e) {
								print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
						}
						
				}
		}

		

		/* Update Status persetujuan di transaksi */
		$data = array(
				'approval'=> 9,
		);
		$sUpt = updateQuery($dbname,'sdm_kpi',$data, "id = '".$notransaksi."'");
		try {
				$owlPDO->exec($sUpt); 
		} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
		}
	break;

	case 'posting':
			if($param['namaatasan']==''){
				exit("Warning: Nama persetujuan / nama atasan harus diisi.");
			}

			// $str = "SELECT sum(bobot) as bobot FROM ".$dbname.".sdm_kpidt1 WHERE idht = ".$param['idkpi']; 
			// $res = fetchdata($str)[0];
			// if($res['bobot']!='100'){
			// 	exit("Warning: Bobot harus 100%, proses dibatalkan.");
			// }
			
			$str = "delete from ".$dbname.".approval WHERE notransaksi = '".$param['idkpi']."' and jenispersetujuan='KPI'"; #exit("error".$str);
			$owlPDO->exec($str);
			$data = array(
				'notransaksi'     => $param['idkpi'],
				'jenispersetujuan'=> 'KPI',
				'level'           => '1',
				'karyawanid'      => $param['namaatasan'],
				'status'          => '0'
			);

           	$queryH = insertQuery($dbname,'approval',$data,array_keys($data)); #exit("error".$queryH);
			$owlPDO->exec($queryH);
			
			
			$data = array(
				'posting'   => '1',
				'approval'   => '9',
				'namaatasan'   => $param['namaatasan'],
				'lastupdate'=> date("Y-m-d H:i:s"),
				'updateby'  => $_SESSION['standard']['userid']
			);
			$where = "id = '".$param['idkpi']."'";
			$query = updateQuery($dbname,'sdm_kpi',$data,$where); //exit("error".$query);
			$owlPDO->exec($query);
	break;
	case 'reject':
		$data = array(
			'status'   => '2',
			'komentar'   => $param['komentar'],
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['idkpi']."' and jenispersetujuan='KPI' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		$data = array(
			'approval'   => '2',
			'posting'   => '0',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['idkpi']."'";
		$query = updateQuery($dbname,'sdm_kpi',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'approve':

		// $str = "SELECT sum(bobot) as bobot FROM ".$dbname.".sdm_kpidt1 WHERE idht = ".$param['idkpi']; 
		// $res = fetchdata($str)[0];
		// if($res['bobot']!='100'){
		// 	exit("Warning: Bobot harus 100%, proses dibatalkan.");
		// }
		
		$data = array(
			'status'   => '1',
			'tanggal'  => date("Y-m-d H:i:s")
		);
		$where = "notransaksi = '".$param['idkpi']."' and jenispersetujuan='KPI' and karyawanid='".$_SESSION['standard']['userid']."'";
		$query = updateQuery($dbname,'approval',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
		
		$data = array(
			'approval'   => '1',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['idkpi']."'";
		$query = updateQuery($dbname,'sdm_kpi',$data,$where); //exit("error".$query);
		$owlPDO->exec($query);
	break;
	case 'unposting':
		$data = array(
			'posting'   => '0',
			'approval'   => '0',
			'lastupdate'=> date("Y-m-d H:i:s"),
			'updateby'  => $_SESSION['standard']['userid']
		);
		$where = "id = '".$param['idkpi']."'";
		$query = updateQuery($dbname,'sdm_kpi',$data,$where); //exit("error".$query);
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
		
		$nm=array("Y"=>"YA","N"=>"TIDAK");
	
		$str = "SELECT * FROM ".$dbname.".sdm_kpi WHERE id = ".$param['id']; 
        $res = fetchdata($str)[0];
		
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=0 style='font-size:13px'>";
		}else{			
			$tab.="<table border=0>";
		}
		$tab.="<tr>
				<td>".$_SESSION['lang']['nama']."</td>
				<td>:</td>
				<td colspan=4>".getKary($res['karyawanid'],'namakaryawan')."</td>
				
				<td width=50px></td>
				
				<td>".$_SESSION['lang']['jabatan']."</td>
				<td>:</td>
				<td colspan=8>".getNamaJabatan($res['jabatan'])."</td>
			</tr>";
		$tab.="<tr>
				<td>".$_SESSION['lang']['lokasitugas']."</td>
				<td>:</td>
				<td colspan=4>".getNamaOrg(getKary($res['karyawanid'],'lokasitugas'))."</td>
				
				<td></td>
				
				<td>".$_SESSION['lang']['departemen']."</td>
				<td>:</td>
				<td colspan=8>".getNamaDept($res['dept'])."</td>
			</tr><tr>
				<td>Tanggal</td>
				<td>:</td>
				<td colspan=4>".tanggalnormal($res['tanggal'])."</td>
				
				<td></td>

				<td>".$_SESSION['lang']['tahun']."</td>
				<td>:</td>
				<td>".$res['tahun']."</td>
				
				
				
			</tr>
			<tr>
				<td>Penilaian</td>
				<td>:</td>
				<td colspan=4>".$arrnilai[$res['penilaian']]."</td>
				
				<td></td>
				<td></td>
				<td></td>
				
				<td></td>
				
				
			</tr>";
		$tab.="</table>";
		
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=1 cellspacing=0 cellpadding=1 class=sortable style='font-size:13px'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		}
	
		$tab.="	<thead>
					<tr class=rowheader>
						<th align=center colspan='3'>".$_SESSION['lang']['kpi']."</th>
						<th align=center>".$_SESSION['lang']['bobot']."<br>(%)</th>
						<th align=center>Target</th>
						<th align=center>Realisasi</th>
						<th align=center>Skor</th>
						<th align=center>Nilai Akhir</th>
					</tr>
				</thead>
				<tbody>";
			
			
			

			$idht = $param['id'];

			$iddt1='';
			$strold = "select * from ".$dbname.".sdm_kpidt1 where idht='".$idht."' order by idkpi";
			$resold = fetchdata($strold);
			foreach($resold as $valold){
				if($iddt1==''){
					$iddt1="'".$valold['idkpi']."'";
				}else{
					$iddt1.=",'".$valold['idkpi']."'";
				}
			}

			$datanilai=array();
			if($iddt1!=''){
				$strold = "select * from ".$dbname.".sdm_kpidt2 where iddt1 in (".$iddt1.") order by iddt1";
				$resold = fetchdata($strold);
				foreach($resold as $valold){
					$datanilai[$valold['iddt1']]['skor']=$valold['skor'];
					$datanilai[$valold['iddt1']]['nilaiakhir']=$valold['nilaiakhir'];
					$datanilai[$valold['iddt1']]['realisasi']=$valold['realisasi'];
				}

			}
			
			

			$str = "select * from ".$dbname.".sdm_kpidt1 where idht='".$idht."' order by idkpi";
			//echo $str;
			$res = fetchdata($str);
			$detail=false;
			if(count($res)>0){
				$dataid='';
				$dataheader=array();
				$dataheader2=array();
				$str = "select a.*,b.jenis from ".$dbname.".sdm_kpidt1 a
				left join  ".$dbname.".sdm_5kpi b on a.idtextkpi=b.id 
				where a.idht='".$idht."' and b.jenis=0 order by idkpi";
				$res = fetchdata($str);			
				foreach($res as $val){
					$dataheader[$val['idtextkpi']]=$val['textkpi'];
					$dataheader2[$val['idtextkpi']]['bobot']=$val['bobot'];
					$dataheader2[$val['idtextkpi']]['idkpi']=$val['idkpi'];
				}
				

				if(count($dataheader)>0){
					$datasubheader=array();
					$datasubheader2=array();
					$str = "select a.*,b.jenis,b.induk from ".$dbname.".sdm_kpidt1 a
					left join ".$dbname.".sdm_5kpi b on a.idtextkpi=b.id  
					where a.idht='".$idht."' and b.jenis=1 order by idkpi";
					//echo $str;
					$res = fetchdata($str);			
					foreach($res as $val){
						$datasubheader[$val['induk']]=1;
						$datasubheader2[$val['induk']][$val['idtextkpi']]['id']=$val['idtextkpi'];
						$datasubheader2[$val['induk']][$val['idtextkpi']]['text']=$val['textkpi'];
						$datasubheader2[$val['induk']][$val['idtextkpi']]['bobot']=$val['bobot'];
						$datasubheader2[$val['induk']][$val['idtextkpi']]['idkpi']=$val['idkpi'];
					}
					

					$datatext=array();
					$datatext2=array();
					$str = "select a.*,b.jenis,b.induk,b.tipepenilaian from ".$dbname.".sdm_kpidt1 a
					left join ".$dbname.".sdm_5kpi  b on a.idtextkpi=b.id 
					where a.idht='".$idht."' and b.jenis=2 order by idkpi";
					$res = fetchdata($str);			
					foreach($res as $val){
						$datatext[$val['induk']]=1;
						$datatext2[$val['induk']][$val['idtextkpi']]['id']=$val['idtextkpi'];
						$datatext2[$val['induk']][$val['idtextkpi']]['text']=$val['textkpi'];
						$datatext2[$val['induk']][$val['idtextkpi']]['bobot']=$val['bobot'];
						$datatext2[$val['induk']][$val['idtextkpi']]['target']=$val['target'];
						$datatext2[$val['induk']][$val['idtextkpi']]['idkpi']=$val['idkpi'];
						$datatext2[$val['induk']][$val['idtextkpi']]['tipepenilaian']=$val['tipepenilaian'];
					}
					

					foreach ($dataheader as $id => $text) {
						if($dataid==''){
							$dataid=$id;
						}else{
							$dataid.="###".$id;
						}
						if(isset($datasubheader[$id])){
							foreach ($datasubheader2[$id] as $valsub) {
								if($dataid==''){
									$dataid=$valsub['id'];
								}else{
									$dataid.="###".$valsub['id'];
								}
								if(isset($datatext2[$valsub['id']])){
									foreach ($datatext2[$valsub['id']] as $valtext) {
										if($dataid==''){
											$dataid=$valtext['id'];
										}else{
											$dataid.="###".$valtext['id'];
										}
									}
								}
							}
						}

						if(isset($datatext[$id])){
							foreach ($datatext2[$id] as $valtext) {
								if($dataid==''){
									$dataid=$valtext['id'];
								}else{
									$dataid.="###".$valtext['id'];
								}
							}
						}

					}
					$datacetak=array();
					$nox=0;
					$tbobot=0;
					$tnilaiakhir=0;
					foreach ($dataheader as $id => $text) {
						
					
						$nox++;
						$nox2=0;
						$tbobot+=$dataheader2[$id]['bobot'];
						$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$id.">
							<td  colspan='3'>".$nox.".".nl2br($text)."</td>
							<td align=right>".$dataheader2[$id]['bobot']."</td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							";
							
						$tab.="</tr>";
						if(isset($datasubheader[$id])){
							foreach ($datasubheader2[$id] as $valsub) {
								$nox2++;
								$nox3=0;
								$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valsub['id'].">
									<td></td>
									<td colspan='2'>".$nox.".".$nox2.".".nl2br($valsub['text'])."</td>
									<td align=right>".$valsub['bobot']."</td>
									<td></td>
									<td></td>
									<td></td>
									<td></td>
									";
									
								$tab.="</tr>";
								if(isset($datatext2[$valsub['id']])){
									foreach ($datatext2[$valsub['id']] as $valtext) {
										$nox3++;
										if(!isset($datanilai[$valtext['idkpi']])){
											$datanilai[$valtext['idkpi']]['nilaiakhir']=0;
											$datanilai[$valtext['idkpi']]['skor']=0;
											$datanilai[$valtext['idkpi']]['realisasi']=0;
										}
										//$tbobot+=$valtext['bobot'];
										$tnilaiakhir+=$datanilai[$valtext['idkpi']]['nilaiakhir'];
										$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valtext['id'].">
											<td></td>
											<td></td>
											<td>".$nox.".".$nox2.".".$nox3.".".nl2br($valtext['text'])."</td>
											<td align=right>".$valtext['bobot']."</td>
											<td align=right>".($valtext['target'])."</td>
											<td align=right>".$datanilai[$valtext['idkpi']]['realisasi']."</td>
											<td align=right>".$datanilai[$valtext['idkpi']]['skor']."</td>
											<td align=right>".$datanilai[$valtext['idkpi']]['nilaiakhir']."</td>
											";	
										
										
											
										$tab.="</tr>";
									}
								}
							}
						}

						if(isset($datatext[$id])){
							foreach ($datatext2[$id] as $valtext) {
								$nox2++;
										if(!isset($datanilai[$valtext['idkpi']])){
											$datanilai[$valtext['idkpi']]['nilaiakhir']=0;
											$datanilai[$valtext['idkpi']]['skor']=0;
											$datanilai[$valtext['idkpi']]['realisasi']=0;
										}

										//$tbobot+=$valtext['bobot'];
										$tnilaiakhir+=$datanilai[$valtext['idkpi']]['nilaiakhir'];

										$tab.="<tr class=rowcontent style=vertical-align:top; id=rowdt".$valtext['id'].">
											<td></td>
											<td colspan='2'>".$nox.".".$nox2.".".nl2br($valtext['text'])."</td>
											<td align=right>".$valtext['bobot']."</td>
											<td align=right>".($valtext['target'])."</td>
											<td align=right>".$datanilai[$valtext['idkpi']]['realisasi']."</td>
											<td align=right>".$datanilai[$valtext['idkpi']]['skor']."</td>
											<td align=right>".$datanilai[$valtext['idkpi']]['nilaiakhir']."</td>
											";	
										
											
										$tab.="</tr>";
							}
						}
					}
				}

			


				$no++;
				$tab.="<tr class=rowcontent style=background-color:#ccfffd;font-weight:bold;>
					<td></td>
					<td align=center>T O T A L</td>
					<td align=center>".$tbobot."</td>
					<td></td><td></td>
					<td align=center></td>
					<td align=center></td>
					<td align=center>".$tnilaiakhir."</td>
					";
				$tab.="</tr>";
			}
			
			
			
			$tab.="</tbody></table>";	
		
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
