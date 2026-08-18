<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method    = checkPostGet('method', '');
$param     = $_POST;if(count($param)==0){$param = $_GET;}
$arrstatus = array('1'=>'AKTIF','0'=>'NON AKTIF');

switch($method){
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['orgtype']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['divisi']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['tipekaryawan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['kodejabatan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['tglberlaku']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['status']."</th>
				<th align=center colspan=4>Max Lembur</th>
				<th align=center rowspan=2>".$_SESSION['lang']['updateby']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['updatetime']."</th>
				<th align=center rowspan=2 class='no-sort'></th>
				<th align=center rowspan=2 class='no-sort'></th>
			</tr>
			<tr class=rowheader>
				<th align=center>Sehari</th>
				<th align=center>Seminggu</th>
				<th align=center>HM/HB</th>
				<th align=center>%Gaji</th>
			</tr>
		</thead>
		<tbody>";
		
		$no=0;
		$str= "select * from ".$dbname.".sdm_validasilembur order by karyawanid desc, jabatan desc, tipekaryawan desc, divisi desc, kodeorg desc";
		$res= fetchdata($str);
		foreach($res as $val){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$no."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$val['tipeorg']."</td>";
			$tab.="<td style='text-align:left;vertical-align:top;' nowrap>".($val['kodeorg']==''?strtoupper($_SESSION['lang']['all']):getNamaOrg($val['kodeorg']))."</td>";
			if(empty($val['karyawanid'])){	
				$tab.="<td style='text-align:left;vertical-align:top;'>".($val['divisi']==''?strtoupper($_SESSION['lang']['all']):getNamaOrg($val['divisi']))."</td>";
				$tab.="<td style='text-align:left;vertical-align:top;'>".($val['tipekaryawan']==''?strtoupper($_SESSION['lang']['all']):getNamaTipeKary($val['tipekaryawan']))."</td>";
				$tab.="<td style='text-align:left;vertical-align:top;'>".($val['jabatan']==''?strtoupper($_SESSION['lang']['all']):getNamaJabatan($val['jabatan']))."</td>";
			}else{
				$tab.="<td style='text-align:left;vertical-align:top;'></td>";
				$tab.="<td style='text-align:left;vertical-align:top;'></td>";
				$tab.="<td style='text-align:left;vertical-align:top;'></td>";
			}
			$karya = '';
			if(!empty($val['karyawanid'])){				
				$n=0;
				$tempkary = explode(",",$val['karyawanid']);
				foreach($tempkary as $kary){
					$n++;
					$e=$n%3;
					if($e==0){
						$karya.=$n.". ".getKary($kary)."<br>";
					}else{
						$karya.=$n.". ".getKary($kary).", ";
					}
				}
			}else{
				$karya = strtoupper($_SESSION['lang']['all']);				
			}
			
			$tab.="<td style='text-align:left;vertical-align:top;'>".$karya."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$val['mulaiberlaku']."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$arrstatus[$val['status']]."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$val['maxkerjasehari']."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$val['maxkerjaseminggu']."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$val['maxlibursehari']."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".$val['persengaji']."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".getNamaKaryawan($val['updateby'])."</td>";
			$tab.="<td style='text-align:center;vertical-align:top;'>".tanggalnormald($val['lastupdate'])."</td>";
			$tab.="<td style='text-align:center;width:25px;vertical-align:top;'>
				<img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editdata('edit','".$val['id']."','".$val['tipeorg']."','".$val['kodeorg']."','".$val['divisi']."','".$val['tipekaryawan']."','".$val['jabatan']."','".$val['karyawanid']."','".tanggalnormal($val['mulaiberlaku'])."','".$val['status']."','".$val['maxkerjasehari']."','".$val['maxkerjaseminggu']."','".$val['maxlibursehari']."','".$val['persengaji']."')\";></td>";
			$tab.="<td style='text-align:center;width:25px;vertical-align:top;'>
				<img src='images/delete_32.png' class='zImgBtn' title='Delete' onclick=del('".$val['id']."');></td>";
			$tab.="</tr>";
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
	
	case 'addnew':
		$tab="";
		foreach($arrstatus as $key => $val){
			if($key=='1'){
				$optstatus.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$optstatus.="<option value=".$key.">".$val."</option>";							
			}
		}
		$optkodeorg="<option value='#'>".$_SESSION['lang']['all']."</option>";
		foreach(getOrgDetail(11) as $key => $val){
			$tipe[getNamaOrg($key,'tipe')]=getNamaOrg($key,'tipe');
		}
		
		$opttipeorg="<option value='#'>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($tipe as $tipeorg){
			$opttipeorg.="<option value=".$tipeorg.">".$tipeorg."</option>";
		}
		
		$optdivisi="<option value='#'>".$_SESSION['lang']['all']."</option>";
		$optjabatan="<option value='#'>".$_SESSION['lang']['all']."</option>";
		$opttipe="<option value='#'>".$_SESSION['lang']['all']."</option>";
		$optkary="<option value='#'>".$_SESSION['lang']['all']."</option>";
		
		$str = "select * from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by id";
		$res = fetchdata($str);
		foreach($res as $val){
			$opttipe.="<option value=".$val['id'].">".$val['tipe']."</option>";
		}
		
		$tab.="<table border=0 cellpadding=2 cellspacing=1>
			<tr>
				<td style=width:150px>".$_SESSION['lang']['orgtype']."</td>
				<td>:</td>
				<td>
					<select class='select2' onchange=getData(this.id); id=tipeorg style='width:350px;'>".$opttipeorg."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kodeorg']."</td>
				<td>:</td>
				<td>
					<select class='select2' onchange=getData(this.id); id=kodeorg style='width:350px;'>".$optkodeorg."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['divisi']."</td>
				<td>:</td>
				<td>
					<select class='select2' onchange=getData(this.id); id=divisi style='width:350px;'>".$optdivisi."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tipekaryawan']."</td>
				<td>:</td>
				<td>
					<select class='select2' onchange=getData(this.id);  id=tipekaryawan style='width:350px;'>".$opttipe."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kodejabatan']."</td>
				<td>:</td>
				<td>
					<select class='select2' onchange=getData(this.id); id=jabatan style='width:350px;'>".$optjabatan."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['namakaryawan']."</td>
				<td>:</td>
				<td>
					<select class='select2' id=karyawanid multiple style='width:350px;'>".$optkary."</select>
				</td>
			</tr>
			<tr>
				<td>Max Lembur</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtextnumber placeholder='Sehari' id=sehari style=\"width:70px;height:30px;font-size:14px;padding-left:5px;text-align:center;\" value=''/>
					<input type=text class=myinputtextnumber placeholder='Seminggu' id=seminggu style=\"width:80px;height:30px;font-size:14px;padding-left:5px;text-align:center;\" value=''/>
					<input type=text class=myinputtextnumber placeholder='HM/HB' id=hmhb style=\"width:78px;height:30px;font-size:14px;padding-left:5px;text-align:center;\" value=''/>
					<input type=text class=myinputtextnumber placeholder='%Gaji' id=persengaji style=\"width:80px;height:30px;font-size:14px;padding-left:5px;text-align:center;\" value=''/>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tglberlaku']."</td>
				<td>:</td>
				<td>
					<input type=text class=myinputtext readonly  id=mulaiberlaku onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style=\"width:150px;height:30px;font-size:14px;padding-left:5px\" value=''/>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['status']."</td>
				<td>:</td>
				<td>
					<select class='select2' id=status style='width:100px;'>".$optstatus."</select>
				</td>
			</tr>
			<tr>
				<td><input type=hidden id=method value=insert></td>
				<td colspan=2 style='padding-left:12px'>
					<input type='hidden' id='idx' value=''>
					<button onclick=simpan(); id='btnsimpan' style='width:150px;height:30px' class=mybutton>".$_SESSION['lang']['save']."</button>
				</td>
			</tr>
		</table>";
		echo $tab;
	break;
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			
			$where = " and id='".$param['id']."'";
			$str = "delete from " . $dbname . ".sdm_validasilembur where 1=1 ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			if($param['kodeorg']==null){$param['kodeorg']='';}
			if($param['divisi']=='#'){$param['divisi']='';}
			if($param['divisi']==null){$param['divisi']='';}
			if($param['jabatan']==null){$param['jabatan']='';}
			if($param['tipekaryawan']==null){$param['tipekaryawan']='';}
			if($param['karyawanid']=='null'){$param['karyawanid']='';}
			$param['mulaiberlaku'] = tanggalsystemn($param['mulaiberlaku']);
			
			
			$where="";
			if($param['kodeorg']!=''){				
				$where=" and kodeorg=''";
			}
			if($param['divisi']!=''){				
				$where=" and divisi=''";
				$where.=" and (kodeorg='".$param['kodeorg']."' or kodeorg='')";
			}
			if($param['tipekaryawan']!=''){				
				$where=" and tipekaryawan=''";
				$where.=" and (divisi='".$param['divisi']."' or divisi='')";
				$where.=" and kodeorg='".$param['kodeorg']."'";
			}
			if($param['jabatan']!=''){				
				$where=" and jabatan=''";
				$where.=" and (tipekaryawan='".$param['tipekaryawan']."' or tipekaryawan='')";
				$where.=" and divisi='".$param['divisi']."'";
				$where.=" and kodeorg='".$param['kodeorg']."'";
			}
			
			
			
			
			$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' ".$where."";
			$res = fetchdata($str);
			foreach($res as $bar){	
				$data = array(
					'status'   => '0',
					'updateby' => $_SESSION['standard']['userid']
				);
				$where = "id='".$bar['id']."'";
				$query = updateQuery($dbname,'sdm_validasilembur',$data,$where); //exit("warningcode".$query);
				$owlPDO->exec($query);
			}
			
			if($param['karyawanid']==''){				
				if($param['kodeorg']!='' and $param['divisi']==''){	
					$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' and kodeorg='".$param['kodeorg']."' and divisi!=''";
					$res = fetchdata($str);
					if(count($res)>0){					
						throw new PDOException("Sudah ada data dengan Divisi bukan Seluruhnya.<br>Proses dibatalkan.");
					}
				}
				if($param['divisi']!='' and $param['tipekaryawan']==''){				
					$where=" and kodeorg='".$param['kodeorg']."'";
					
					$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' ".$where." and divisi='".$param['divisi']."' and tipekaryawan!=''";
					$res = fetchdata($str);
					if(count($res)>0){					
						throw new PDOException("Sudah ada data dengan Tipe Karyawan bukan Seluruhnya.<br>Proses dibatalkan.");
					}
				}
				if($param['tipekaryawan']!='' and $param['jabatan']==''){				
					$where=" and divisi='".$param['divisi']."'";
					$where.=" and kodeorg='".$param['kodeorg']."'";
					
					$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' ".$where." and tipekaryawan='".$param['tipekaryawan']."' and jabatan!=''";
					$res = fetchdata($str);
					if(count($res)>0){					
						throw new PDOException("Sudah ada data dengan Jabatan bukan Seluruhnya.<br>Proses dibatalkan.");
					}
				}
			}
			
			if($param['karyawanid']!=''){
				$param['divisi']=$param['tipekaryawan']=$param['jabatan']="";
			}
			$data = array(
				'tipeorg'        => $param['tipeorg'],
				'kodeorg'        => $param['kodeorg'],
				'divisi'         => $param['divisi'],
				'tipekaryawan'   => $param['tipekaryawan'],
				'jabatan'        => $param['jabatan'],
				'karyawanid'     => $param['karyawanid'],
				'mulaiberlaku'   => $param['mulaiberlaku'],
				'status'         => $param['status'],
				'maxkerjasehari' => $param['sehari'],
				'maxkerjaseminggu' => $param['seminggu'],
				'maxlibursehari' => $param['hmhb'],
				'persengaji' => $param['persengaji'],
				'updateby'       => $_SESSION['standard']['userid']
			);
			
			$query = insertQuery($dbname,'sdm_validasilembur',$data,array_keys($data));
			// exit("error".$query);
			$owlPDO->exec($query);
			
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	case 'update':
		try{
			$owlPDO->beginTransaction();
			if($param['kodeorg']==null){$param['kodeorg']='';}
			if($param['divisi']=='#'){$param['divisi']='';}
			if($param['divisi']==null){$param['divisi']='';}
			if($param['jabatan']==null){$param['jabatan']='';}
			if($param['tipekaryawan']==null){$param['tipekaryawan']='';}
			if($param['karyawanid']=='null'){$param['karyawanid']='';}
			$param['mulaiberlaku'] = tanggalsystemn($param['mulaiberlaku']);
			
			$where="";
			if($param['kodeorg']!=''){				
				$where=" and kodeorg=''";
			}
			if($param['divisi']!=''){				
				$where=" and divisi=''";
				$where.=" and (kodeorg='".$param['kodeorg']."' or kodeorg='')";
			}
			if($param['tipekaryawan']!=''){				
				$where=" and tipekaryawan=''";
				$where.=" and (divisi='".$param['divisi']."' or divisi='')";
				$where.=" and kodeorg='".$param['kodeorg']."'";
			}
			if($param['jabatan']!=''){				
				$where=" and jabatan=''";
				$where.=" and (tipekaryawan='".$param['tipekaryawan']."' or tipekaryawan='')";
				$where.=" and divisi='".$param['divisi']."'";
				$where.=" and kodeorg='".$param['kodeorg']."'";
			}
			
			
			
			
			$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' ".$where."";
			//exit("warningcode".$str);
			$res = fetchdata($str);
			foreach($res as $bar){	
				$data = array(
					'status'   => '0',
					'updateby' => $_SESSION['standard']['userid']
				);
				$where = "id='".$bar['id']."'";
				$query = updateQuery($dbname,'sdm_validasilembur',$data,$where); 
				$owlPDO->exec($query);
			}
			
			if($param['karyawanid']==''){				
				if($param['kodeorg']!='' and $param['divisi']==''){	
					$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' and kodeorg='".$param['kodeorg']."' and divisi!=''";
					$res = fetchdata($str);
					if(count($res)>0){					
						throw new PDOException("Sudah ada data dengan Divisi bukan Seluruhnya.<br>Proses dibatalkan.");
					}
				}
				if($param['divisi']!='' and $param['tipekaryawan']==''){				
					$where=" and kodeorg='".$param['kodeorg']."'";
					
					$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' ".$where." and divisi='".$param['divisi']."' and tipekaryawan!=''";
					$res = fetchdata($str);
					if(count($res)>0){					
						throw new PDOException("Sudah ada data dengan Tipe Karyawan bukan Seluruhnya.<br>Proses dibatalkan.");
					}
				}
				if($param['tipekaryawan']!='' and $param['jabatan']==''){				
					$where=" and divisi='".$param['divisi']."'";
					$where.=" and kodeorg='".$param['kodeorg']."'";
					
					$str = "select * from " . $dbname . ".sdm_validasilembur where 1=1 and mulaiberlaku ='".$param['mulaiberlaku']."' ".$where." and tipekaryawan='".$param['tipekaryawan']."' and jabatan!=''";
					$res = fetchdata($str);
					if(count($res)>0){					
						throw new PDOException("Sudah ada data dengan Jabatan bukan Seluruhnya.<br>Proses dibatalkan.");
					}
				}
			}
			
			
			if($param['karyawanid']!=''){
				$param['divisi']=$param['tipekaryawan']=$param['jabatan']="";
			}
			$data = array(
				'tipeorg'        => $param['tipeorg'],
				'kodeorg'        => $param['kodeorg'],
				'divisi'         => $param['divisi'],
				'tipekaryawan'   => $param['tipekaryawan'],
				'jabatan'        => $param['jabatan'],
				'karyawanid'     => $param['karyawanid'],
				'mulaiberlaku'   => $param['mulaiberlaku'],
				'status'         => $param['status'],
				'maxkerjasehari' => $param['sehari'],
				'maxkerjaseminggu' => $param['seminggu'],
				'maxlibursehari' => $param['hmhb'],
				'persengaji' => $param['persengaji'],
				'updateby'       => $_SESSION['standard']['userid']
			);
			
			$where = "id='".$param['id']."'";
			$query = updateQuery($dbname,'sdm_validasilembur',$data,$where); #exit("warningcode".$query);
			$owlPDO->exec($query);
			
			
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "warning, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'getData':
		echo getData($param);
	break;
}

function getData($param){
	global $dbname;
	global $owlPDO;
		
	$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optdivisi="<option value='#'>".$_SESSION['lang']['all']."</option>";
	$optjabatan="<option value=''>".$_SESSION['lang']['all']."</option>";
	$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
	$optkary="<option value=''>".$_SESSION['lang']['all']."</option>";
	
	if($param['sumber']=='tipeorg'){			
		foreach(getOrgDetail(11) as $key => $val){
			if(getNamaOrg($key,'tipe')==$param['tipeorg']){				
				$d=getNamaOrg($key,'induk');
				if($d!=$n){			
					$optkodeorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				if($param['selected']==$key){
					$optkodeorg.="<option value=".$key." selected>".$key." - ".$val."</option>";
				}else{					
					$optkodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
				}
				$n=$d;
				if($d!=$n){			
					$optkodeorg.="</optgroup>";
				}
			}
		}
	}
	
	if($param['sumber']=='kodeorg'){
		$str = "select distinct subbagian from ".$dbname.".datakaryawan where 1=1 and lokasitugas='".$param['kodeorg']."' order by subbagian";
		$res = fetchdata($str);
		foreach($res as $val){
			if($val['subbagian']==''){
				$val['subbagian']="UMUM";
			}
			if($param['selected']==$val['subbagian']){
				$optdivisi.="<option value=".$val['subbagian']." selected>".$val['subbagian']." - ".getNamaOrg($val['subbagian'])."</option>";
			}else{					
				$optdivisi.="<option value=".$val['subbagian'].">".$val['subbagian']." - ".getNamaOrg($val['subbagian'])."</option>";
			}
		}
	}
	
	$where="";
	if($param['kodeorg']!=''){		
		$where.=" and lokasitugas='".$param['kodeorg']."'";
	}
	if($param['divisi']=='UMUM'){		
		$param['divisi']="";
	}
	if($param['divisi']!='#'){		
		$where.=" and subbagian='".$param['divisi']."'";
	}
	
	if($param['sumber']=='divisi'){		
		#tipe kary
		$str = "select * from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by id";
		$res = fetchdata($str);
		foreach($res as $val){
			if($param['selected']==$val['id']){
				$opttipe.="<option value=".$val['id']." selected>".($val['tipe'])."</option>";
			}else{					
				$opttipe.="<option value=".$val['id'].">".($val['tipe'])."</option>";
			}
		}
	}
	
	
	if($param['tipekaryawan']!=''){		
		$where.=" and tipekaryawan='".$param['tipekaryawan']."'";
	}
	
	if($param['sumber']=='tipekaryawan'){		
		# jabatan
		$str = "select distinct kodejabatan from ".$dbname.".datakaryawan where 1=1 ".$where."";
		$res = fetchdata($str);
		foreach($res as $val){
			if($param['selected']==$val['kodejabatan']){
				$optjabatan.="<option value=".$val['kodejabatan']." selected>".getNamaJabatan($val['kodejabatan'])."</option>";
			}else{					
				$optjabatan.="<option value=".$val['kodejabatan'].">".getNamaJabatan($val['kodejabatan'])."</option>";
			}
		}
	}		
	if($param['jabatan']!=''){		
		$where.=" and kodejabatan='".$param['jabatan']."'";
	}
	
	# kary
	$str = "select karyawanid, nik, namakaryawan from ".$dbname.".datakaryawan where 1=1 ".$where."";
	$res = fetchdata($str);
	foreach($res as $val){
		$optkary.="<option value=".$val['karyawanid'].">".$val['nik']." - ".$val['namakaryawan']."</option>";
	}
	
	return $optkodeorg."##".$optdivisi."##".$optjabatan."##".$opttipe."##".$optkary;
	
}
?>
