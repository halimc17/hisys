<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$param = $_POST;
if(count($param)==0){$param = $_GET;}
$id       = checkPostGet('id','');
$method   = checkPostGet('method','');
$jab      = getPostingJabatan('wo'); 
$arrjenis = ['newasset'=>'Asset Baru','pemelasset'=>'Perbaikan Asset','nonasset'=>'Non Asset'];
$path     = "fileupload/workorder/";

switch ($method) {
	case 'caribarang':
		$data    = $_GET;
        $perPage = 5;
		$page    = checkPostGet('page','');
		if($page==0){$page=1;}
		
		$where="";
		// if($data['jenis']=='newasset'){
		// }
		// if($data['jenis']=='pemelasset'){
			// $where.=" and kodebarang like '9%'";
		// }
		// if($data['jenis']=='nonasset'){
			// $where.=" and kodebarang like '3%'";
		// }
		$where.=" and substr(kodebarang,1,1) in ('3','8','9')";		
		$str = "select * from ".$dbname.".log_5masterbarang where 1=1 ".$where." and (namabarang like '%".$param['search']."%' or kodebarang like '%".$param['search']."%')";
		#limit ".$perPage.",".$page."";
		
		$res = fetchdata($str);
        $select['total_count'] = count($res);
        $selectajax = array();
		foreach($res as $row){
			 $selectajax[] = array(
                'id' => $row['kodebarang'],
                'text' => $row['kodebarang']." - ".$row['namabarang']
            );
		}
        $select['items'] = $selectajax;
		echo json_encode($select);
	break;	
	case 'updatebrg':
		try {
            $owlPDO->beginTransaction();
			
			if($param['notransaksi']==''){
				throw new PDOException("Notransaksi tidak boleh kosong.");
			}
			if($param['kodebarang']==''){
				throw new PDOException("Kodebarang tidak boleh kosong.");
			}
			if($param['jumlah']==''){
				throw new PDOException("Jumlah tidak boleh kosong.");
			}

			
			$data = array(
				'kodebarang'    => $param['kodebarang'],
				'satuan'        => $param['satuan'],
				'jumlah'        => $param['jumlah'],
				'updateby'      => $_SESSION['standard']['userid']
			);
			$where = "notransaksi='".$param['notransaksi']."' and kodebarang='".$param['kodebarangold']."'";
			$query = updateQuery($dbname,'vhc_workordermat',$data,$where); //exit("error".$query);
			$owlPDO->exec($query);
			
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warning : " . addslashes($e->getMessage());
			$error = true;
        }
	break;
	case 'insertbrg':
		$error = false;
		try {
            $owlPDO->beginTransaction();
			
			if($param['notransaksi']==''){
				throw new PDOException("Notransaksi tidak boleh kosong.");
			}
			if($param['kodebarang']==''){
				throw new PDOException("Kodebarang tidak boleh kosong.");
			}
			if($param['jumlah']==''){
				throw new PDOException("Jumlah tidak boleh kosong.");
			}

			$data = array(
				'notransaksi'   => $param['notransaksi'],
				'kodebarang'    => $param['kodebarang'],
				'satuan'        => $param['satuan'],
				'jumlah'        => $param['jumlah'],
				'keterangan'    => '',
				'noreferensi'   => '',
				'createdby'     => $_SESSION['standard']['userid'],
				'createdtime'   => date('Y-m-d H:i:s'),
				'updateby'      => $_SESSION['standard']['userid']
			);

			$queryH = insertQuery($dbname,'vhc_workordermat',$data,array_keys($data)); //exit("error".$queryH);
			$owlPDO->exec($queryH);
			
			
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warning : " . addslashes($e->getMessage());
			$error = true;
        }
		
	break;	
	case 'updatekeg':
		try {
            $owlPDO->beginTransaction();
			
			if($param['lokasi']==''){
				$param['lokasi']=$param['kodeorg'];
			}
			if($param['alokasibiaya']==''){
				$param['alokasibiaya']=$param['lokasi'];
			}
			
			$data = array(
				'lokasikerja'               => $param['lokasi'],
				'alokasibiaya'              => $param['alokasibiaya'],
				'kodekegiatan'              => $param['kegiatan'],
				'satuan'                    => $param['satuan'],
				'jumlah'                    => $param['jumlah'],
				'tanggalkeg'                => tanggalsystemn($param['tanggal']),
				'keterangan'                => $param['keterangan'],
				'noreferensi'               => '',
				'updateby'                  => $_SESSION['standard']['userid']
			);
			$where = "notransaksi='".$param['notransaksi']."' and nomor='".$param['idkeg']."'";
			$query = updateQuery($dbname,'vhc_workorderkeg',$data,$where); //exit("error".$query);
			$owlPDO->exec($query);
			
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warning : " . addslashes($e->getMessage());
			$error = true;
        }
	break;
	case 'insertkeg':
		$error = false;
		try {
            $owlPDO->beginTransaction();
			
			if($param['lokasi']==''){
				$param['lokasi']=$param['kodeorg'];
			}
			if($param['alokasibiaya']==''){
				$param['alokasibiaya']=$param['lokasi'];
			}
			
			$str = "SELECT max(nomor) as nomor FROM ".$dbname.".vhc_workorderkeg WHERE notransaksi='".$param['notransaksi']."'";
			$res = fetchdata($str);
			if($res[0]['nomor']>0){
				$nomor=intval($res[0]['nomor'])+1;
			}else{
				$nomor='1';
			}
			
			$data = array(
				'notransaksi'               => $param['notransaksi'],
				'nomor'                     => $nomor,
				'lokasikerja'               => $param['lokasi'],
				'alokasibiaya'              => $param['alokasibiaya'],
				'kodekegiatan'              => $param['kegiatan'],
				'satuan'                    => $param['satuan'],
				'jumlah'                    => $param['jumlah'],
				'tanggalkeg'                => tanggalsystemn($param['tanggal']),
				'keterangan'                => $param['keterangan'],
				'noreferensi'               => '',
				'createdby'                 => $_SESSION['standard']['userid'],
				'createdtime'               => date('Y-m-d H:i:s'),
				'updateby'                  => $_SESSION['standard']['userid']
			);

			$queryH = insertQuery($dbname,'vhc_workorderkeg',$data,array_keys($data)); //exit("error".$queryH);
			$owlPDO->exec($queryH);
			
			
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warning : " . addslashes($e->getMessage());
			$error = true;
        }
		
	break;	
	case 'insert':
		$error = false;
		try {
            $owlPDO->beginTransaction();
			
			$str = "SELECT max(convert(substring_index(notransaksi,'/',-1),unsigned integer)) as nomor FROM ".$dbname.".vhc_workorderht WHERE kodeorg='".$param['kodeorg']."' and tanggal like '".substr(tanggalsystemn($param['tanggal']),0,7)."%'";
			$res = fetchdata($str);
			if($res[0]['nomor']>0){
				$nomor=addZero($res[0]['nomor']+1,4);
			}else{
				$nomor='0001';										
			}
			if($param['namaasset']=='' and $param['kodeasset']!=''){
				$str = "select * from ".$dbname.".sdm_daftarasset where kodeasset = '".$param['kodeasset']."'"; #exit("error".$str);
				$res = fetchdata($str);
				foreach($res as $bar){
					$param['namaasset']=$bar['namasset'];
					$param['kelompokasset']=$bar['tipeasset'];
					$param['subkelasset']=$bar['subtipe'];
					$param['tipelokasi']=$bar['tipelokasi'];
					$param['satuan']="UNIT";
					$param['jumlah']="1";
				}
			}
			if(empty($param['notransaksi'])){
				$notransaksi = str_replace("-","",tanggalsystemn($param['tanggal']))."/".$param['kodeorg']."/WO/".$nomor;
				
				// echo"<pre>";
				// print_r($param);
				// echo"</pre>";
				// exit("error");
				
				$data = array(
					'notransaksi'          => $notransaksi,
					'kodeorg'              => $param['kodeorg'],
					'tanggal'              => tanggalsystemn($param['tanggal']),
					'jenis'                => $param['jenis'],
					'kelasset'             => $param['kelompokasset'],
					'subklasset'           => $param['subkelasset'],
					'tipelokasi'           => $param['tipelokasi'],
					'kodeasset'            => $param['kodeasset'],
					'namaasset'            => $param['namaasset'],
					'satuan'               => $param['satuan'],
					'jumlah'               => $param['jumlah'],
					'tanggalmulai'         => tanggalsystemn($param['tanggaldari']),
					'tanggalsampai'        => tanggalsystemn($param['tanggalsampai']),
					'keterangan'           => $param['keterangan'],
					'noreferensi'          => '',
					'createdby'            => $_SESSION['standard']['userid'],
					'createdtime'          => date('Y-m-d H:i:s'),
					'updateby'             => $_SESSION['standard']['userid']
				);

				$queryH = insertQuery($dbname,'vhc_workorderht',$data,array_keys($data)); #exit("error".$queryH);
				$owlPDO->exec($queryH);
				
			}else{
				$notransaksi = $param['notransaksi'];
				
				$data = array(
					'kodeorg'       => $param['kodeorg'],
					'tanggal'       => tanggalsystemn($param['tanggal']),
					'jenis'         => $param['jenis'],
					'kelasset'      => $param['kelompokasset'],
					'subklasset'    => $param['subkelasset'],
					'tipelokasi'    => $param['tipelokasi'],
					'kodeasset'     => $param['kodeasset'],
					'namaasset'     => $param['namaasset'],
					'satuan'        => $param['satuan'],
					'jumlah'        => $param['jumlah'],
					'tanggalmulai'  => tanggalsystemn($param['tanggaldari']),
					'tanggalsampai' => tanggalsystemn($param['tanggalsampai']),
					'keterangan'    => $param['keterangan'],
					'noreferensi'   => '',
					'updateby'      => $_SESSION['standard']['userid']
				);
				$where = "notransaksi='".$notransaksi."'";
				$query = updateQuery($dbname,'vhc_workorderht',$data,$where); //exit("error".$query);
				$owlPDO->exec($query);
				
			}
			
         
            $owlPDO->commit();
        } catch(PDOException $e) {        
        	$owlPDO->rollback();
            echo "Warning : " . addslashes($e->getMessage());
			$error = true;
        }
		
		if($error == false){
			echo $notransaksi;
		}
	break;
	case'loaddatadetail':
		$where="";
		switch($param['jenis']){
			case'newasset':
				$hidden="style=display:none;";
				$akunkelasset=makeOption($dbname,'sdm_5tipeasset','kodetipe,akunak',"kodetipe='".$param['kelompokasset']."'");
				
				$where.=" and noakun like '".$akunkelasset[$param['kelompokasset']]."%'";
			break;
			case'pemelasset':
				$hidden="style=display:none;";
				if(getNamaOrg($param['kodeorg'],'tipe')=='KEBUN'){
					$where.=" and noakun like '7%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='PABRIK'){
					$where.=" and noakun like '7%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='TC'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='RND'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='KANWIL'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='HOLDING'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='BULKING'){
					$where.=" and noakun like '81%'";
				}
			break;
			case'nonasset':
				$hidden="";
				$whr="";
				if(getNamaOrg($param['kodeorg'],'tipe')=='KEBUN'){
					$where.=" and noakun like '7%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='PABRIK'){
					$where.=" and noakun like '7%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='TC'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='RND'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='KANWIL'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='HOLDING'){
					$where.=" and noakun like '82%'";
				}
				if(getNamaOrg($param['kodeorg'],'tipe')=='BULKING'){
					$where.=" and noakun like '81%'";
				}
				$whr.=" and induk ='".$param['kodeorg']."'";
				
			break;
		}
		
		$optkeg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".setup_kegiatan where 1=1 ".$where." and status='1' order by kodekegiatan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$d=getNamaAkun(substr($bar['noakun'],0,3));
			if($d!=$n){			
				$optkeg.="<optgroup label='".$d."'>";
			}
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkeg.="</optgroup>";
			}
		}
		
		$optorg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".organisasi where 1=1 ".$whr." and namaorganisasi not like '%NON AKTI%' order by kodeorganisasi";
		$res = fetchdata($str);
		if(!empty($res)){			
			foreach($res as $bar){
				$d=getNamaOrg(substr($bar['kodeorganisasi'],0,4));
				if($d!=$n){			
					$optorg.="<optgroup label='".$d."'>";
				}
				$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				$n=$d;
				if($d!=$n){			
					$optorg.="</optgroup>";
				}
			}
		}else{
			$optorg.="<option value=".$param['kodeorg'].">".$param['kodeorg']." - ".getNamaOrg($param['kodeorg'])."</option>";		
		}
		
	
		$frm[0]="<fieldset style=float:left><legend>".$_SESSION['lang']['kegiatan']."</legend>
			<table border=0>
				<tr ".$hidden.">
					<td style=width:90px>".$_SESSION['lang']['lokasi']."</td>
				  	<td>:</td>
				  	<td>
						<select id='lokasi' class='select2' onchange=getalokasibyy(this); style='width:205px;'>".$optorg."</select>
				  	</td>
					
					<td style=width:100px>".$_SESSION['lang']['alokasibiaya']."</td>
				  	<td>:</td>
				  	<td>
						<select id='alokasibiaya' onchange=getkegiatan(this);  class='select2' style='width:205px;'>".$optorg."</select>
				  	</td>
				</tr>
				<tr>
					<td style=width:90px>".$_SESSION['lang']['kegiatan']."</td>
				  	<td>:</td>
				  	<td>
						<select id='kegiatan' class='select2' onchange=getsatuan('keg',this.value,'satuandt'); style='width:205px;'>".$optkeg."</select>
				  	</td>
					
					<td style=width:100px>".$_SESSION['lang']['keterangan']."</td>
				  	<td>:</td>
				  	<td>
				  		<input id=keterangandt class=myinputtext style='width:200px;'>
				  	</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
				  	<td>:</td>
				  	<td><input type=text id=tanggaldt class=myinputtext readonly onmousemove=setCalendar(this.id); maxlength=10 style=width:200px;></td>
					
					<td>".$_SESSION['lang']['jumlah']."</td>
				  	<td>:</td>
				  	<td nowrap>
				  		<input type=number id=jumlahdt class=myinputtextnumber style='width:92px;'>
						<select id='satuandt' class='select2' style='width:105px;'>".$optkeg."</select>
				  	</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td  colspan=20>
						<input type=hidden id=methodkeg value=insertkeg>
						<input type=hidden id=idkeg value=''>
						<button class=mybutton onclick=\"simpankeg();\">".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=\"batalkeg();\">".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
			</fieldset>
			<div style=clear:both></div>
			<fieldset style=float:left><legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellspacing=1 cellpadding=5 class=sortable>
				<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center>".$_SESSION['lang']['lokasi']."</th>
						<th align=center>".$_SESSION['lang']['alokasibiaya']."</th>
						<th align=center>".$_SESSION['lang']['kegiatan']."</th>
						<th align=center>".$_SESSION['lang']['keterangan']."</th>
						<th align=center>".$_SESSION['lang']['tanggal']."</th>
						<th align=center>".$_SESSION['lang']['satuan']."</th>
						<th align=center>".$_SESSION['lang']['jumlah']."</th>
						<th align=center>".$_SESSION['lang']['createby']."</th>
						<th align=center>".$_SESSION['lang']['updateby']."</th>
						<th align=center colspan=2>".$_SESSION['lang']['action']."</th>
					</tr>
				</thead>
				<tbody id=containerdetailkeg></tbody>
				<tfoot></tfoot>
				</table>
			</fieldset>
			";
			
		$frm[1]="<fieldset style=float:left><legend>".$_SESSION['lang']['material']."</legend>
			<table border=0>
				<tr>
					<td>".$_SESSION['lang']['kodebarang']."</td>
				  	<td>:</td>
				  	<td>
				  		<select id='kodebarang' class='select2' onchange=getsatuan('brg',this.value,'satuanbrg')></select>
						<input hidden id=kodebarangold>
				  	</td>
					
					<td>".$_SESSION['lang']['jumlah']."</td>
				  	<td>:</td>
				  	<td nowrap>
				  		<input type=number id=jumlahbrg class=myinputtextnumber style='width:92px;'>
						<select id='satuanbrg' class='select2' style='width:100px;'></select>
				  	</td>
				</tr>
				<tr>
					<td colspan=2></td>
					<td  colspan=20>
						<input type=hidden id=methodbrg value=insertbrg>
						<input type=hidden id=idbrg value=''>
						<button class=mybutton onclick=\"simpanbrg();\">".$_SESSION['lang']['save']."</button>
						<button class=mybutton onclick=\"batalbrg();\">".$_SESSION['lang']['cancel']."</button>
					</td>
				</tr>
			</table>
			</fieldset><div style=clear:both></div>
			<fieldset style=float:left><legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellspacing=1 cellpadding=5 class=sortable>
				<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center>".$_SESSION['lang']['kodebarang']."</th>
						<th align=center>".$_SESSION['lang']['namabarang']."</th>
						<th align=center>".$_SESSION['lang']['satuan']."</th>
						<th align=center>".$_SESSION['lang']['jumlah']."</th>
						<th align=center>".$_SESSION['lang']['createby']."</th>
						<th align=center>".$_SESSION['lang']['updateby']."</th>
						<th align=center colspan=2>".$_SESSION['lang']['action']."</th>
					</tr>
				</thead>
				<tbody id=containerdetailbrg></tbody>
				<tfoot></tfoot>
				</table>
			</fieldset>";
				
		$frm[2]="<fieldset style=float:left><legend>".$_SESSION['lang']['upload']."</legend>
				<table border=0 >
					<tr>
						<td>Filename</td>
						<td>:</td>
						<td>
							<input class=myinputtext type='file' name='upload' id='upload' >
						</td>
					</tr>
					<tr id=rowstatus style=display:none;>
						<td style=vertical-align:top>Status</td>
						<td style=vertical-align:top>:</td>
						<td>
							<progress id='progressBar' value='0' max='100' style='width:300px;display:none;'></progress>
							<p id='status'></p>
							<p id='loaded_n_total'></p>
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button id=btnsubmit class=mybutton onclick=\"submitfile('".$param['notransaksi']."')\">Submit</button>
							<button id=btnbtlupload class=mybutton onclick=\"batalupload()\">Batal</button>
						</td>
					</tr>
				</table>
				</fieldset>
				<div style=clear:both></div>
				<fieldset style=float:left><legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' cellpadding=5 border='0'>
					<thead>
					<tr class=rowheader>
						<th align='center' width=30px>No.</th>
						<th align='center'>File Type</th>
						<th align='center'>Filename</th>
						<th align='center' width=30px colspan=2>Action</th>
					</tr>
					</thead>
					<tbody id='listfiles'>
					</tbody>
				</table>
			</fieldset>";
			
			
			
		$hfrm[0]=$_SESSION['lang']['kegiatan'];
		$hfrm[1]=$_SESSION['lang']['material'];
		$hfrm[2]=$_SESSION['lang']['upload'];
		drawTab('FRM',$hfrm,$frm,'','100%');
	break;
	case 'getkegiatan':
		$whr=$where=""; 
		$whr=" and induk ='".$param['lokasi']."'";
		if(getNamaOrg($param['kodeorg'],'tipe')=='KEBUN'){
			if(getNamaOrg($param['alokasibiaya'],'tipe')=='BLOK' or getNamaOrg($param['alokasibiaya'],'tipe')=='BIBITAN'){
				if(getBlok($param['alokasibiaya'],'statusblok')=='TM'){					
					$where.=" and (noakun like '621%' or noakun like '611%')";
				}elseif(getBlok($param['alokasibiaya'],'statusblok')=='TBM'){					
					$where.=" and noakun like '126%'";
				}elseif(getBlok($param['alokasibiaya'],'statusblok')=='TB'){					
					$where.=" and noakun like '126%'";
				}elseif(getBlok($param['alokasibiaya'],'statusblok')=='BBT'){
					if(substr($param['alokasibiaya'],4,4)=='BBPN'){
						$where.=" and noakun like '12801%'";
					}else{						
						$where.=" and noakun like '12802%'";
					}
				}
			}else{				
				$where.=" and noakun like '7%'";
			}
		}
		if(getNamaOrg($param['kodeorg'],'tipe')=='PABRIK'){
			if(getNamaOrg($param['alokasibiaya'],'tipe')=='STENGINE'){				
				$where.=" and noakun like '63%'";
			}else{				
				$where.=" and noakun like '7%'";
			}
		}
		if(getNamaOrg($param['kodeorg'],'tipe')=='TC'){
			$where.=" and noakun like '82%'";
		}
		if(getNamaOrg($param['kodeorg'],'tipe')=='RND'){
			$where.=" and noakun like '82%'";
		}
		if(getNamaOrg($param['kodeorg'],'tipe')=='KANWIL'){
			$where.=" and noakun like '82%'";
		}
		if(getNamaOrg($param['kodeorg'],'tipe')=='HOLDING'){
			$where.=" and noakun like '82%'";
		}
		if(getNamaOrg($param['kodeorg'],'tipe')=='BULKING'){
			$where.=" and noakun like '81%'";
		}
		
		$optkeg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".setup_kegiatan where 1=1 ".$where." and status='1' order by kodekegiatan";
		$res = fetchdata($str);
		foreach($res as $bar){
			$d=getNamaAkun(substr($bar['noakun'],0,3));
			if($d!=$n){			
				$optkeg.="<optgroup label='".$d."'>";
			}
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkeg.="</optgroup>";
			}
		}
		
		echo $optkeg;
	break;
	case 'getalokasibyy':
		$whr=" and induk ='".$param['lokasi']."'";
		
		$optorg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optorg.="<option value=".$param['lokasi'].">".$param['lokasi']." - ".getNamaOrg($param['lokasi'])."</option>";
		$str = "select * from ".$dbname.".organisasi where 1=1 ".$whr." and namaorganisasi not like '%NON AKTI%' order by kodeorganisasi"; //exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			// $d=getNamaOrg(substr($bar['kodeorganisasi'],0,6));
			// if($d!=$n){			
				// $optorg.="<optgroup label='".$d."'>";
			// }
			$optorg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			// $n=$d;
			// if($d!=$n){			
				// $optorg.="</optgroup>";
			// }
		}
		
		echo $optorg;
	break;
	case 'submitfile':
		try {
		$owlPDO->beginTransaction();
		$data = $_POST;
		if(count($data)==0){
			$data = $_GET;			
		}
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $_FILES['file']['name'];
				
				#cek duplikasi nama file
				$tempnomor="";
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."' and kriteriaefil='WO'";
				$res=fetchData($str);
				if(count($res)>0){
					$tempnomor=" (".(count($res)+1).")";
					$filename=$filename.$tempnomor;
					// throw new PDOException("Nama file sudah pernah digunakan, silahkan di rename terlebih dahulu.".$filename);
				}
				
				$str="select * from ".$dbname.".listfileupload where namafile = '".$filename."' and kriteriaefil='WO' and notransaksi='".$param['notransaksi']."'";
				$res=fetchData($str);
				if(count($res)>0){
					throw new PDOException("File sudah pernah diupload.");
				}
				
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.zip')||($filetype=='.rar')){
					$str = "insert into ".$dbname.".listfileupload (`notransaksi`, `namafile`, `formaticon`, `kriteriaefil`, `status`, `createdby`, `createdtime`)
					values ('".$param['notransaksi']."','".$filename."','".$filetype."','WO','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
					$owlPDO->exec($str);
					
					if (!file_exists($path)){
						mkdir($path, 0777, true);
					}
					
					file_put_contents($path.$filename,$file_tmpname);
				}else{
					throw new PDOException("Format file upload harus .jpg, .jpeg, .png, .pdf, .xls, .xlsx, .doc, .docx");
				}
				if (!file_exists($path.$filename)) {
					throw new PDOException("Upload file gagal.");
				}
			}
		}else{
			throw new PDOException("Upload file gagal.");
		}
		#execute
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();
	}
	break;
	case 'loadfiles':
		$str= "select * from ".$dbname.".kebun_aktifitas where notransaksi = '".$param['notransaksi']."'";
		$res= fetchData($str);
		$jurnal = $res[0]['jurnal'];
		
		$no = 0;
		$tab= "";
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
		$res= fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
				if($jurnal==0){					
					$tab.="<td align=center width=30px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
					
					$tab.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" ></td>";
				}else{
					$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				}
				$tab.="</tr>";
			}
		}
		echo $tab;
	break;
	case 'deletefile':
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."' and kriteriaefil='WO'";
		try{
			$owlPDO->exec($str);
			$pathx = $path.$param['namafile'];
			unlink($pathx);
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'viewfile':
		$tab="";
		$str= "select * from ".$dbname.".listfileupload where id = '".$param['idfile']."'";
		$res= fetchData($str);
		if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
			exit("Warning: Tidak bisa ditampilkan, silahkan download.");
		}
		
		if($res[0]['formaticon']=='.pdf'){
			$tab.="<embed src='".$path.$res[0]['namafile']."' style='width:100%;height:97%;' type='application/pdf'>";
		}else{			
			$tab.="<img src='".$path.$res[0]['namafile']."' style='width:100%;height:auto;'>";
		}
		
		echo $tab;
	break;	
	case 'loaddatadetailbrg':
		$str = "SELECT * FROM ".$dbname.".vhc_workordermat WHERE notransaksi='".$param['notransaksi']."'";
		$res = fetchdata($str);	
		foreach($res as $key=>$val){
			$no++;
			$tab.="<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$val['kodebarang']."</td>
			<td >".getNamaBrg($val['kodebarang'])."</td>
			<td align=center>".$val['satuan']."</td>
			<td align=center>".$val['jumlah']."</td>
			<td align=center style=font-size:10px;>".getNamaKaryawan($val['createdby'])."<br>".tanggalnormald($val['createdtime'])."</td>
			<td align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['lastupdate'])."</td>";
			
			$tab.="<td align=center><img src=images/application/application_edit.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"editdetailbrg('".$val['notransaksi']."','".$val['kodebarang']."','".getNamaBrg($val['kodebarang'])."','".$val['satuan']."','".$val['jumlah']."');\"></td>";
			$tab.="<td align=center><img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedetailbrg('".$val['notransaksi']."','".$val['kodebarang']."');\"></td>";
			$tab."</tr>";
		}
		
		echo $tab;
	break;
	case 'loaddatadetailkeg':
		$str = "SELECT * FROM ".$dbname.".vhc_workorderkeg WHERE notransaksi='".$param['notransaksi']."' order by kodekegiatan desc, tanggalkeg desc";
		$res = fetchdata($str);	
		foreach($res as $key=>$val){
			$no++;
			$tab.="<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$val['lokasikerja']."</td>
			<td >".getNamaOrg($val['alokasibiaya'])."</td>
			<td >".$val['kodekegiatan']." - ".getNamaKeg($val['kodekegiatan'])."</td>
			<td >".$val['keterangan']."</td>
			<td align=center>".tanggalnormal($val['tanggalkeg'])."</td>
			<td align=center>".$val['satuan']."</td>
			<td align=center>".$val['jumlah']."</td>
			<td align=center style=font-size:10px;>".getNamaKaryawan($val['createdby'])."<br>".tanggalnormald($val['createdtime'])."</td>
			<td align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['lastupdate'])."</td>";
			
			$tab.="<td align=center><img src=images/application/application_edit.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"editdetailkeg('".$val['notransaksi']."','".$val['nomor']."','".$val['lokasikerja']."','".$val['alokasibiaya']."','".$val['kodekegiatan']."','".$val['satuan']."','".$val['jumlah']."','".tanggalnormal($val['tanggalkeg'])."','".$val['keterangan']."');\"></td>";
			$tab.="<td align=center><img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedetailkeg('".$val['notransaksi']."','".$val['nomor']."');\"></td>";
			$tab."</tr>";
		}
		
		echo $tab;
	break;
	case 'getsatuan':
		if($param['jenis']=='keg'){			
			$optkelasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str = "select * from ".$dbname.".setup_kegiatan where kodekegiatan = '".$param['value']."' order by kodekegiatan";
			$res = fetchdata($str);
			foreach($res as $bar){
				$optkelasset.="<option value='".$bar['satuan']."'>".$bar['satuan']."</option>";
			}
		}
		if($param['jenis']=='brg'){
			$str = "select * from ".$dbname.".log_5masterbarang where kodebarang = '".$param['value']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$optkelasset.="<option value='".$bar['satuan']."'>".$bar['satuan']."</option>";
			}
		}
		echo $optkelasset;
	break;
	case 'getsubklasset':
		$optkelasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_5subtipeasset where kodetipe = '".$param['kelompokasset']."' order by namasub";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optkelasset.="<option value='".$bar['kodesub']."'>".$bar['namasub']."</option>";
		}
		echo $optkelasset;
	break;
	case 'getkodeasset':
		$optkelasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		if($param['tipelokasi']!=''){			
			$where=" and tipelokasi='".$param['tipelokasi']."'";
		}
		
		$str = "select * from ".$dbname.".sdm_daftarasset where posisiasset = '".$param['kodeorg']."' and subtipe = '".$param['subkelasset']."' and tipeasset = '".$param['kelompokasset']."' ".$where." order by namasset"; #exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$kodelama="";
			if(trim($bar['kodeassetlama'])!=''){
				$kodelama=" - ".trim($bar['kodeassetlama']);
			}
			
			
			$optkelasset.="<option value='".$bar['kodeasset']."'>".$bar['kodeasset'].$kodelama." - ".$bar['namasset']."</option>";
		}
		echo $optkelasset;
	break;
	case 'gettipelokasi':
		$disabled="disabled";
		$optipelok="<option value=''>Disabled</option>";
		$str = "select * from ".$dbname.".keu_5tipelokasiasset where tipelokasi='".getNamaOrg($param['kodeorg'],'tipe')."' order by namalokasi";
		$res = fetchdata($str);
		if(count($res)>0){
			$disabled="";
			$optipelok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";					
			foreach($res as $bar){
				$optipelok.="<option value='".$bar['kodelokasi']."'>".$bar['namalokasi']."</option>";
			}
		}
		
		$optkelasset="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select * from ".$dbname.".sdm_daftarasset where posisiasset = '".$param['kodeorg']."' and tipeasset in ('BG','IL','IS','PA','PR') and status='1' order by namasset"; #exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$kodelama="";
			if(trim($bar['kodeassetlama'])!=''){
				$kodelama=" - ".trim($bar['kodeassetlama']);
			}
			$optkelasset.="<option value='".$bar['kodeasset']."'>".$bar['kodeasset'].$kodelama." - ".$bar['namasset']."</option>";
		}
		
		echo $optipelok."##".$disabled."##".$optkelasset;
	break;
	
	case 'getdetailasset':
		$str = "select * from ".$dbname.".sdm_daftarasset where kodeasset = '".$param['kodeasset']."' and status='1' "; #exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){
			$nama=$bar['namasset'];
			$subtipe=$bar['subtipe'];
			$tipeasset=$bar['tipeasset'];
			$tipelokasi=$bar['tipelokasi'];
		}
		
		echo $nama."##".$subtipe."##".$tipeasset."##".$tipelokasi;
	break;
	case 'hapus':
		$where = " and notransaksi='".$param['notransaksi']."'";
		#= Query Hapus Transaksinya
		$strht = "delete from ".$dbname.".vhc_workorderht WHERE 1=1 ".$where."";
		
		#= Query Hapus approvalnya juga
		$strApp = "delete from ".$dbname.".approval WHERE notransaksi='".$param['notransaksi']."'";
		// exit('warning');
		
		try{
			#= Eksekusi Hapus Transaksinya
			$owlPDO->exec($strht);
			#= Eksekusi Hapus approvalnya 
			$owlPDO->exec($strApp);

		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		
		$str = "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and kriteriaefil='WO'"; #exit("error".$str);
		$res = fetchdata($str);
		foreach($res as $bar){			
			$sql="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$bar['namafile']."' and kriteriaefil='WO'";
			try{
				$owlPDO->exec($sql);
				$pathx = $path.$bar['namafile'];
				unlink($pathx);
			}catch(PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
		
	break;
	case 'loaddata':
		$where = "";
		if ($param['notransaksi'] != ''){
			$where .= " AND notransaksi like '%".$param['notransaksi']."%'";
		}
		if ($param['kodeorg'] != ''){
			$where .= " AND kodeorg='".$param['kodeorg']."'";
		}
		if ($param['namaasset'] != ''){
			$where .= " AND namaasset like '%".$param['namaasset']."%'";
		}
		if ($param['ket'] != ''){
			$where .= " AND keterangan like '%".$param['ket']."%'";
		}
		if ($param['jenis'] != ''){
			$where .= " AND jenis = '".$param['jenis']."'";
		}
		
		if ($param['post'] != '') {
			$where .= " AND posting='".$param['post']."'";
		}
		
		$tab="<br><table border=0 cellspacing=1 cellpadding=5 class=sortable>
					<thead>
						<tr class=rowheader>
							<th align=center>".$_SESSION['lang']['nourut']."</th>
							<th align=center>".$_SESSION['lang']['notransaksi']."</th>
							<th align=center>".$_SESSION['lang']['kodeorg']."</th>
							<th align=center>".$_SESSION['lang']['tanggal']."</th>
							<th align=center>".$_SESSION['lang']['jenis']."</th>
							<th align=center>".$_SESSION['lang']['namaasset']."</th>
							<th align=center>".$_SESSION['lang']['keterangan']."</th>
							<th align=center>".$_SESSION['lang']['createby']."</th>
							<th align=center>".$_SESSION['lang']['updateby']."</th>
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

		$str = "SELECT COUNT(*) as jmlhrow FROM ".$dbname.".vhc_workorderht WHERE 1=1 ".$where; 
        $res = fetchdata($str);
        foreach($res as $bar){
            $jlhbrs = $bar['jmlhrow'];
        }
        
		$arrstatus=array('0'=>'','1'=>'Disetujui','2'=>'Ditolak');
        $no = $offset+1;
		
		$str = "SELECT * FROM ".$dbname.".vhc_workorderht WHERE 1=1 ".$where." ORDER BY notransaksi desc LIMIT ".$offset.",".$limit;
		$res = fetchdata($str);
		foreach($res as $key=>$val){
			$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=center>".$val['notransaksi']."</td>
					<td>".$val['kodeorg']." - ".getNamaOrg($val['kodeorg'])."</td>
					<td align=center>".tanggalnormal($val['tanggal'])."</td>
					<td>".$arrjenis[$val['jenis']]."</td>
					<td>".$val['namaasset']."</td>
					<td>".$val['keterangan']."</td>
					<td align=center style=font-size:10px;>".getNamaKaryawan($val['createdby'])."<br>".tanggalnormald($val['createdtime'])."</td>
					<td align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['lastupdate'])."</td>";
					
					if($val['posting']=='1'){
						$tab.="<td align=center></td>";
						$tab.="<td align=center></td>";
						if(in_array($_SESSION['empl']['jabatan'],$jab)){
							$icon="images/icons/04/16/04.png";
							$title="Unposting";
							$unpost=" onclick=\"unposting('".$val['id']."');\" ";
						}else {
							$icon="images/icons/04/16/02.png";
							$title="Posted";
							$unpost='';
						}
						$tab.="<td align=center><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
					}else{						
						$tab.="<td align=center>
							<img src=images/application/application_edit.png class=zImgBtn title='Edit Data' caption='Edit' onclick=\"fillField('".$val['notransaksi']."','".$val['kodeorg']."','".tanggalnormal($val['tanggal'])."','".$val['jenis']."','".$val['kelasset']."','".$val['subklasset']."','".$val['lokasi']."','".$val['tipelokasi']."','".$val['kodeasset']."','".$val['namaasset']."','".$val['satuan']."','".$val['jumlah']."','".tanggalnormal($val['tanggalmulai'])."','".tanggalnormal($val['tanggalsampai'])."','".$val['keterangan']."');\">
						</td>";
						$tab.="<td align=center>
							<img src=images/application/application_delete.png class=zImgBtn title='Hapus Data' caption='Delete' onclick=\"deletedata('".$val['notransaksi']."');\">
						</td>";
						$tab.="<td align=center>
							<img src='images/skyblue/posting.png' class='zImgBtn' title='Posting' onclick=\"posting('".$val['notransaksi']."');\">
						</td>";
					}
					
					$tab.="<td align=center>
						<img src=images/pdf.jpg class=zImgBtn title='Print PDF' caption='Print PDF' onclick=\"pdf('".$val['notransaksi']."');\">
					</td>
					<td align=center>
						<img src=images/zoom.png class=zImgBtn title='Lihat Detail' caption='Detail' onclick=\"detail('".$val['notransaksi']."');\">
					</td>
				</tr>";
            $no += 1;
		}
		
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		

		$tab .= "</tbody></table>";

		echo $tab;
	break;
	case 'posting':
		if(in_array($_SESSION['empl']['jabatan'],$jab)){
			$data = array(
				'posting'   => '1',
				'lastupdate'=> date("Y-m-d H:i:s"),
				'updateby'  => $_SESSION['standard']['userid']
			);
			$where = "notransaksi = '".$param['notransaksi']."'";
			$query = updateQuery($dbname,'vhc_workorderht',$data,$where); //exit("error".$query);
			$owlPDO->exec($query);
		}else {
			$err="Anda tidak memiliki otorisasi untuk melakukan posting.<br>Berikut adalah jabatan yg bisa melakukan posting :<br>";
			foreach($jab as $jabatan){
				$nomor++;
				$err.=$nomor.". ".getNamaJabatan($jabatan)."<br>";
			}
			
			exit("Error: ".$err);
		}
	break;
	case'detail':
		
		$nm=array("Y"=>"YA","N"=>"TIDAK");
		
		$str = "select * from ".$dbname.".keu_5tipelokasiasset";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optipelok[$bar['kodelokasi']]=$bar['namalokasi'];
		}
		
		$str = "select * from ".$dbname.".sdm_5subtipeasset";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optkelasset[$bar['kodesub']]=$bar['namasub'];
		}
		
		$akunkelasset=makeOption($dbname,'sdm_5tipeasset','kodetipe,namatipe');
		
		
		$str = "SELECT * FROM ".$dbname.".vhc_workorderht WHERE notransaksi = '".$param['notransaksi']."'"; 
        $res = fetchdata($str)[0];
		
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=0 style='font-size:13px'>";
		}else{			
			$tab.="<table border=0>";
		}
		$tab.="<tr>
				<td>".$_SESSION['lang']['notransaksi']."</td>
				<td>:</td>
				<td>".$res['notransaksi']."</td>
				
				<td>".$_SESSION['lang']['kodeorg']."</td>
				<td>:</td>
				<td>".getNamaOrg($res['kodeorg'])."</td>
			</tr>";
		$tab.="<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>".tanggalnormal($res['tanggal'])."</td>
				
				<td>".$_SESSION['lang']['jenis']."</td>
				<td>:</td>
				<td>".$arrjenis[$res['jenis']]."</td>
			</tr>
			<tr>	
				<td>".$_SESSION['lang']['namaasset']."</td>
				<td>:</td>
				<td>".$res['namaasset']."</td>
				
				<td>".$_SESSION['lang']['keterangan']."</td>
				<td>:</td>
				<td>".$res['keterangan']."</td>
			</tr>
			<tr>
				<td>Kelompok Asset</td>
				<td>:</td>
				<td>".$akunkelasset[$res['kelasset']]."</td>
				
				<td>Sub Kel Asset</td>
				<td>:</td>
				<td>".$optkelasset[$res['subklasset']]."</td>
			</tr>
			<tr>
				<td>Tipe Lokasi</td>
				<td>:</td>
				<td>".$optipelok[$res['tipelokasi']]."</td>
				
				<td>Jumlah</td>
				<td>:</td>
				<td>".$res['jumlah']." ".$res['satuan']."</td>
			</tr>
			<tr>
				<td>Tanggal</td>
				<td>:</td>
				<td>".tanggalnormal($res['tanggalmulai'])." s/d ".tanggalnormal($res['tanggalsampai'])."</td>
				
			</tr>";
		$tab.="</table>";
		
		
		
		$tab.="<br><label style=font-weight:bold>".$_SESSION['lang']['kegiatan']."</label>";
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=1 cellspacing=0 cellpadding=1 class=sortable style='font-size:13px'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		}
	
		$tab.="<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center>".$_SESSION['lang']['lokasi']."</th>
						<th align=center>".$_SESSION['lang']['alokasibiaya']."</th>
						<th align=center>".$_SESSION['lang']['kegiatan']."</th>
						<th align=center>".$_SESSION['lang']['keterangan']."</th>
						<th align=center>".$_SESSION['lang']['tanggal']."</th>
						<th align=center>".$_SESSION['lang']['satuan']."</th>
						<th align=center>".$_SESSION['lang']['jumlah']."</th>
						<th align=center>".$_SESSION['lang']['createby']."</th>
						<th align=center>".$_SESSION['lang']['updateby']."</th>
					</tr>
				</thead>
				<tbody>";
		$str = "SELECT * FROM ".$dbname.".vhc_workorderkeg WHERE notransaksi='".$param['notransaksi']."' order by kodekegiatan desc, tanggalkeg desc";
		$res = fetchdata($str);	
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=10 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{			
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$val['lokasikerja']."</td>
				<td >".getNamaOrg($val['alokasibiaya'])."</td>
				<td >".$val['kodekegiatan']." - ".getNamaKeg($val['kodekegiatan'])."</td>
				<td >".$val['keterangan']."</td>
				<td align=center>".tanggalnormal($val['tanggalkeg'])."</td>
				<td align=center>".$val['satuan']."</td>
				<td align=center>".$val['jumlah']."</td>
				<td align=center style=font-size:10px;>".getNamaKaryawan($val['createdby'])."<br>".tanggalnormald($val['createdtime'])."</td>
				<td align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['lastupdate'])."</td>";
				$tab."</tr>";
			}
		}
		
		$tab.="</tbody>";
		$tab.="</table>";
		
		$tab.="<br><label style=font-weight:bold>".$_SESSION['lang']['material']."</label>";
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=1 cellspacing=0 cellpadding=1 class=sortable style='font-size:13px'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		}
		$tab.="<thead>
					<tr class=rowheader>
						<th align=center>".$_SESSION['lang']['nourut']."</th>
						<th align=center>".$_SESSION['lang']['kodebarang']."</th>
						<th align=center>".$_SESSION['lang']['namabarang']."</th>
						<th align=center>".$_SESSION['lang']['satuan']."</th>
						<th align=center>".$_SESSION['lang']['jumlah']."</th>
						<th align=center>".$_SESSION['lang']['createby']."</th>
						<th align=center>".$_SESSION['lang']['updateby']."</th>
					</tr>
				</thead>
			<tbody>";
				
		$str = "SELECT * FROM ".$dbname.".vhc_workordermat WHERE notransaksi='".$param['notransaksi']."'";
		$res = fetchdata($str);	
		$no=0;
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=7 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=center>".$val['kodebarang']."</td>
				<td >".getNamaBrg($val['kodebarang'])."</td>
				<td align=center>".$val['satuan']."</td>
				<td align=center>".$val['jumlah']."</td>
				<td align=center style=font-size:10px;>".getNamaKaryawan($val['createdby'])."<br>".tanggalnormald($val['createdtime'])."</td>
				<td align=center style=font-size:10px;>".getNamaKaryawan($val['updateby'])."<br>".tanggalnormald($val['lastupdate'])."</td>";
				$tab."</tr>";
			}	
		}
		$tab.="</tbody>";
		$tab.="</table>";
		
		$tab.="<br><label style=font-weight:bold>".$_SESSION['lang']['upload']."</label>";
		if($param['tipeprint']=='pdf'){			
			$tab.="<table border=1 cellspacing=0 cellpadding=1 class=sortable style='font-size:13px'>";
		}else{			
			$tab.="<table border=0 cellspacing=1 cellpadding=5 class=sortable>";
		}
		$tab.="<thead>
			<tr class=rowheader>
				<th align='center' width=30px>No.</th>
				<th align='center'>File Type</th>
				<th align='center'>Filename</th>
				<th align='center'>#</th>
			</tr>
			</thead>
			<tbody>";
		
		$str= "select * from ".$dbname.".listfileupload where notransaksi = '".$param['notransaksi']."' and status='1'";
		$res= fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
					</td>";
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$val['id']."')\">".$val['namafile']."</td>";
				$tab.="<td align=center width=30px colspan=2><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn title='download'></a></td>";
				$tab.="</tr>";
			}
		}	

		$tab.="</tbody>";
		$tab.="</table>";
		
		if($param['tipeprint']=='pdf'){
			
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("wo", array("Attachment" => false));
		}else{			
			echo $tab;
		}
	break;
}
