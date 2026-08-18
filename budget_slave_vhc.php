<?php 
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once 'dompdf/PHPExcel.php';
require_once 'dompdf/PHPExcel/IOFactory.php';

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
$jab = getPostingJabatan('budget');

$proses      =checkPostGet('proses','');
$kdTraksi    =checkPostGet('kdTraksi','');
$kdVhc       =checkPostGet('kdVhc','');
$thnBudget   =checkPostGet('thnBudget','');
$kodeOrg     =checkPostGet('kdOrg','');
$kdtrk       =checkPostGet('kdtrk','');
$thnbdget    =checkPostGet('thnbdget','');
$jmlhPerson  =checkPostGet('jmlhPerson','');
$kdGol       =checkPostGet('kdGol','');
$hkEfektif   =checkPostGet('hkEfektif','');
$tipeBudget  =checkPostGet('tipeBudget','');
$totBiaya    =checkPostGet('totBiaya','');
$nmBrg       =checkPostGet('nmBrg','');
$klmpkBrg    =checkPostGet('klmpkBrg','');
$idData      =checkPostGet('idData','');
$kdBudget    =checkPostGet('kdBudget','');
$kdBrg       =checkPostGet('kdBrg','');
$jmlhBrg     =checkPostGet('jmlhBrg','');
$satuanBrg   =checkPostGet('satuanBrg','');
$totHarga    =checkPostGet('totHarga','');

$kdBudgetB   =checkPostGet('kdBudgetB','');
$noAkun      =checkPostGet('noAkun','');
$totBiayaB   =checkPostGet('totBiayaB','');
$kdBudgetS   =checkPostGet('kdBudgetS','');
$kdWorkshop  =checkPostGet('kdWorkshop','');
$jmlhJam     =checkPostGet('jmlhJam','');
$totHargaJam =checkPostGet('totHargaJam','');
$kodeVhc     =checkPostGet('kodeVhc','');
$kunci       =checkPostGet('kunci','');


$tahunbudget =checkPostGet('tahunbudget','');
$tahuntanam  =checkPostGet('tahuntanam','');
$kodeblok    =checkPostGet('kodeblok','');
$tipebudget  =checkPostGet('tipebudget','');
$noakun      =checkPostGet('noakun','');
$kegiatan    =checkPostGet('kegiatan','');
$volume      =checkPostGet('volume','');
$satuanvolume=checkPostGet('satuanvolume','');
$rotasi      =checkPostGet('rotasi','');    
$sebaran     =checkPostGet('sebaran','');    
$kodeVhc     =checkPostGet('kodeVhc','');    
$kodeorg     =checkPostGet('kodeorg','');    
$jeniskend   =checkPostGet('jeniskend','');    
$traksi      =checkPostGet('traksi','');    

$optNmBrg =makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optNm    =makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optNmAkun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmkode   =makeOption($dbname,'bgt_kode','kodebudget,nama');

$where2=" kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' and tipebudget='TRK' and tahunbudget='".$thnBudget."'";

switch($proses){
	case'fileSelected':
		if($param['tahun']==''){
			exit("Warning : Tahun budget wajib diisi.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode traksi wajib diisi.");
		}
		
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' and closed=1 ";
		$res=fetchData($str);
		foreach($res as $val){
			$harga[$val['kodebarang']]=$val['hargasatuan'];
		}
			
		$data = $_POST;
		
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$file = $_FILES['file']['tmp_name'];		
			
			if($filetype=='.xlsx'){
				$load = PHPExcel_IOFactory::load($file);
				$sheets = $load->getActiveSheet()->toArray(null,true,true,true);
				$arritem=array();
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=9 align=center><button id=btnsubmit class=mybutton onclick=\"fileSelected('simpan')\">SaveAll</button></td>";
				$tab.="</tr>";
				foreach ($sheets as $noitem => $sheet){
					if($noitem>1 and $sheet['A']!=''){
						if($param['jenis']=='simpan'){
							try {
							$owlPDO->beginTransaction();
								$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$sheet['A']."'");
								
								$kodebudget = "M-".substr($sheet['A'],0,3);
								
								$str = "select * from ".$dbname.".bgt_budget where tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."' and tipebudget='".$param['tipebgt']."' and kodebudget='".$kodebudget."' and kodebarang='".$sheet['A']."' and kodevhc='".$param['kodevhc']."'";
								$res = fetchdata($str);
								if(count($res)>0){
									$data = array(
										'tahunbudget'=> $param['tahun'],
										'kodeorg'    => $param['kodeorg'],
										'tipebudget' => $param['tipebgt'],
										'kodebudget' => $kodebudget,
										'kodevhc'    => $param['kodevhc'],
										'rupiah'     => $sheet['B']*$harga[$sheet['A']],
										'kodebarang' => $sheet['A'],
										'regional'   => $region,
										'updateby'   => $_SESSION['standard']['userid'],
										'jumlah'     => $sheet['B'],
										'satuanj'    => $nmsat[$sheet['A']]
									);
									
									$cols = array();
									foreach($data as $key=>$row) {
										$cols[] = $key;
									}
									$where = "kunci='".$res[0]['kunci']."'";
									$str = updateQuery($dbname,'bgt_budget',$data,$where);
									if($sheet['B']*$harga[$sheet['A']]>0){
										$owlPDO->exec($str);
									}
								}else{
									$str="insert into ".$dbname.".bgt_budget (tahunbudget, kodeorg, tipebudget, kodebudget,kodevhc,rupiah, kodebarang, regional, updateby,jumlah,satuanj) 
									values('".$param['tahun']."','".$param['kodeorg']."','".$param['tipebgt']."','".$kodebudget."','".$param['kodevhc']."','".$sheet['B']*$harga[$sheet['A']]."','".$sheet['A']."','".$region."','".$_SESSION['standard']['userid']."','".$sheet['B']."','".$nmsat[$sheet['A']]."')";
									if($sheet['B']*$harga[$sheet['A']]>0){										
										$owlPDO->exec($str);
									}
								}
							$owlPDO->commit();
							} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		
						}else{							
							$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$sheet['A']."'");
							$nmsat=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$sheet['A']."'");
							$no++;
							if($harga[$sheet['A']]<=0){
								$tab.="<tr class=rowcontent style=color:red;>";
							}else{								
								$tab.="<tr class=rowcontent>";
							}
							
							$tab.="<td align=center>".$no."</td>";
							$tab.="<td align=center>".$param['tahun']."</td>";
							$tab.="<td align=center>".$region."</td>";
							$tab.="<td align=center>".$sheet['A']."</td>";
							$tab.="<td align=left>".$nmbrg[$sheet['A']]."</td>";
							$tab.="<td align=center>".$nmsat[$sheet['A']]."</td>";
							$tab.="<td align=right>".$sheet['B']."</td>";
							$tab.="<td align=right>".number_format($harga[$sheet['A']])."</td>";
							$tab.="<td align=right>".number_format($sheet['B']*$harga[$sheet['A']])."</td>";
							$tab.="</tr>";
							$total+=$sheet['B']*$harga[$sheet['A']];
						}
					}
				}
				
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center colspan=8>TOTAL</td>";
				$tab.="<td align=right>".number_format($total)."</td>";
				$tab.="</tr>";
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
		
		echo $tab;
	break;
	case'showupload':
		$tab="";
		// $tab.="<fieldset><legend>Upload / Download</legend>
		$tab.="<table border=0>
			<tr>
				<td>Download</td>
				<td>:</td>
				<td><button class=mybutton onclick=\"downloadmaster()\">Master Barang</button></td>
				<td colspan=4><button class=mybutton ><a href='tool_slave_getExample.php?form=BGTVHC' target='frame'>Template</a></button></td>
			</tr>
			<tr>
				<td>Upload</td>
				<td>:</td>
				<td colspan=6>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td colspan=7>
					<button id=btnsubmit class=mybutton onclick=\"fileSelected()\">Preview</button>
				</td>
			</tr>
		</table>";
		// $tab.="</fieldset>";
		// $tab.="<fieldset>
			// <legend>".$_SESSION['lang']['list']."</legend>";
		$tab.="
			<table class='sortable' cellspacing='1' border='0' cellpadding=3>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center'>Tahun</th>
					<th align='center'>Regional</th>
					<th align='center'>Kode Barang</th>
					<th align='center'>Nama Barang</th>
					<th align='center'>Satuan</th>
					<th align='center'>Jumlah</th>
					<th align='center'>Harga</th>
					<th align='center'>Rupiah</th>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table></div>
		</fieldset> ";

		echo $tab;
	break;
	case'downloadmaster':
		if($param['tahun']==''){
			exit("Warning : Tahun budget wajib diisi.");
		}
		if($param['kodeorg']==''){
			exit("Warning : Kode traksi wajib diisi.");
		}
	
		$tab="";
		$tab.="
			<table class='sortable' cellspacing='1' border='1'>
				<thead>
				<tr class=rowheader>
					<th align='center' width=30px>No.</th>
					<th align='center'>Kode Barang</th>
					<th align='center'>Nama Barang</th>
					<th align='center'>Satuan</th>
					<th align='center'>Harga</th>
				</tr>
				</thead>
				<tbody>";
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($param['kodeorg'],0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$param['tahun']."' and closed=1 ";
		$val=fetchData($str);
		foreach($val as $res){
			$sDt="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
			$nm=fetchData($sDt)[0];
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$res['kodebarang']."</td>";
			$tab.="<td>".$nm['namabarang']."</td>";
			$tab.="<td>".$nm['satuan']."</td>";
			$tab.="<td align=right>".@number_format($res['hargasatuan'])."</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody>
		</table>";
		
		$nop = "masterbarang.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("masterbarang", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();

	break;
	case'copyblok':
		$vhctujuan=checkPostGet('vhctujuan','');    
		$kodevhcsumber=checkPostGet('kodevhcsumber','');    
        
		
        $dzArr=Array();
        # list data budget sumber
        $str="select * from ".$dbname.".bgt_budget where tahunbudget = '".$tahunbudget."' and kodevhc = '".$kodevhcsumber."' and tipebudget = '".$tipebudget."' and kodebudget != 'SUPERVISI' order by kodebudget"; 
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $no=0;
        while($bar= $res->fetch()){
            $dzArr[$bar->kunci]['tahunbudget']=$bar->tahunbudget;
            $dzArr[$bar->kunci]['kodeorg']=$bar->kodeorg;
            $dzArr[$bar->kunci]['tipebudget']=$bar->tipebudget;
            $dzArr[$bar->kunci]['kodebudget']=$bar->kodebudget;
            $dzArr[$bar->kunci]['kegiatan']=$bar->kegiatan;
            $dzArr[$bar->kunci]['noakun']=$bar->noakun;
            $dzArr[$bar->kunci]['volume']=$bar->volume;
            $dzArr[$bar->kunci]['satuanv']=$bar->satuanv;
            $dzArr[$bar->kunci]['rupiah']=$bar->rupiah;
            $dzArr[$bar->kunci]['kodevhc']=$bar->kodevhc;
            $dzArr[$bar->kunci]['kodebarang']=$bar->kodebarang;
            $dzArr[$bar->kunci]['rotasi']=$bar->rotasi;
            $dzArr[$bar->kunci]['regional']=$bar->regional;
            $dzArr[$bar->kunci]['jumlah']=$bar->jumlah;
            $dzArr[$bar->kunci]['satuanj']=$bar->satuanj;
            $dzArr[$bar->kunci]['keterangan']=$bar->keterangan;
            $dzArr[$bar->kunci]['tutup']=$bar->tutup;
        }
		
		
        if(!empty($dzArr))foreach($dzArr as $arey){
            $tahunbudget=$tahunbudget;
            $kodevhc=$vhctujuan;
            $tipebudget=$tipebudget;
            $kodeorg=$arey['kodeorg'];
            $kodebudget=$arey['kodebudget'];
            $kegiatan=$arey['kegiatan'];
			if(is_null($kegiatan)){
				$kegiatan='null';
			}
            $noakun=$arey['noakun'];
			if(is_null($noakun)){
				$noakun='null';
			}
            $volume=$arey['volume'];
            $satuanv=$arey['satuanv'];
            $rupiah=$arey['rupiah'];
            $kodebarang=$arey['kodebarang'];
            $regional=$arey['regional'];
            $jumlah=$arey['jumlah'];
            $satuanj=$arey['satuanj'];
            $tutup=$arey['tutup'];

            $sIns="insert into ".$dbname.".bgt_budget (tahunbudget,kodeorg, tipebudget, kodebudget, kegiatan, noakun, volume, satuanv,rupiah, kodevhc, kodebarang, rotasi, regional, updateby, jumlah, satuanj,keterangan, tutup) values
            ('".$tahunbudget."','".$kodeorg."','".$tipebudget."','".$kodebudget."',".$kegiatan.",".$noakun.",'".$volume."','".$satuanv."','".$rupiah."','".$kodevhc."','".$kodebarang."',null,'".$regional."','".$_SESSION['standard']['userid']."','".$jumlah."','".$satuanj."',null,'".$tutup."')"; #exit("error".$sIns);
            try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
        } // end of foreach

    break;
	case'getVhc':
		
		$optVhc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".vhc_5master where kodetraksi='".$kdTraksi."' and kodevhc in (select kodevhc from ".$dbname.".bgt_vhc_jam_vw where tahunbudget='".$param['thnBdget']."' and jumlahjam>'0')";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("Warning : Silahkan isi terlebih dahulu total jam kendaraan melalui menu : ANGGARAN > TRANSAKSI > BUDGET TRAKSI > 3. TOTAL JAM KENDARAAN");
		}
		foreach($res as $rVhc){
			if($rVhc['nopol']!=''){
				$rVhc['nopol']=" - ".$rVhc['nopol'];
			}
			if($rVhc['detailvhc']!=''){
				$rVhc['detailvhc']=" - ".$rVhc['detailvhc'];
			}
			if($kdVhc!=''){
				$optVhc.="<option value='".$rVhc['kodevhc']."' ".($kdVhc==$rVhc['kodevhc']?'selected':'').">".$rVhc['kodevhc']."".$rVhc['nopol']."".$rVhc['detailvhc']."</option>";
			}else{
				$optVhc.="<option value='".$rVhc['kodevhc']."'>".$rVhc['kodevhc']."".$rVhc['nopol']."".$rVhc['detailvhc']."</option>";
			}
		}
		echo $optVhc;
		// exit("error");
	break;
    case'cekSave':
		if($thnBudget=='' || $kodeOrg=='' || $kdVhc==''){
			exit("Error : Budget year, Org code, Vhc code are obligatory");
		}
		if(strlen($thnBudget)<4){
			exit("Error : Budget year required");
		}
		
		$str = "select sum(jumlahjam) as jlh from ".$dbname.".bgt_vhc_jam where tahunbudget = '".$thnBudget."' and kodevhc  = '".$kdVhc."'";
		$res = fetchdata($str);
		if($res[0]['jlh']==0){
			exit("Errorcode : Total jam kendaraan belum diinput.");
		}
		
		$str="select distinct tutup from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and tutup='1' and kodevhc='".$param['kdVhc']."'";
		$res = fetchdata($str);
		if($res[0]['tutup']!=0){
			exit("Errorcode :  Budget year ".$thnBudget." has been closed.");
		}
		
		
		$str="select distinct * from ".$dbname.".bgt_hk where tahunbudget='".$thnBudget."' and unit = '".substr($kodeOrg,0,4)."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$thrlb    =$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
			$thke     =$bar['harisetahun']-$thrlb;
			$tsim     =$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
			$tothke   =$thke-($bar['jlhcuti']+$tsim);
			$hkEfektip=$tothke;
		}	
		
		$optWs="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$pt = getNamaOrg(substr($kodeOrg,0,4),'induk');
		
		$str="select distinct kodews from ".$dbname.".bgt_ws_jam where tahunbudget='".$thnBudget."' and kodetraksi='".$kodeOrg."'";
		$str="select distinct kodews from ".$dbname.".bgt_ws_jam where tahunbudget='".$thnBudget."' and kodetraksi in (select kodeorganisasi from ".$dbname.".organisasi where  tipe='TRAKSI' and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$pt."'))";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optWs.="<option value='".$bar['kodews']."'>".$bar['kodews']." - ".$optNm[$bar['kodews']]."</option>";
		}
		
		
		$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($kodeOrg,0,4)."'");
		if($tipeorg[substr($kodeOrg,0,4)]=='PABRIK'){
			$where=" and golongan like 'EXPL%'";
		}elseif($tipeorg[substr($kodeOrg,0,4)]=='BULKING'){
			$where=" and golongan like 'EXPLBULK%'";
		}else{
			$where=" and golongan like 'SDM%'";
		}
		
		$optKdbdgt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		//$str="select kodebudget,nama from ".$dbname.".bgt_kode where 1=1 ".$where." and  order by kodebudget asc";
		
		$nmkode=makeOption($dbname,'bgt_kode','kodebudget,nama');
		
		$str="select golongan from ".$dbname.".bgt_upah where 1=1 ".$where." and tahunbudget='".$thnBudget."' and kodeorg= '".substr($kodeOrg,0,4)."' and jumlah>'0' order by golongan asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optKdbdgt.="<option value=".$bar['golongan'].">".$nmkode[$bar['golongan']]."</option>";
		}
		
		$str = "select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kodeOrg,0,4)."'";
		$res = fetchdata($str);
		$regional = $res[0]['regional'];
		
		$optbarang="<option value=''></option>";
		$str="select distinct kodebarang from ".$dbname.".bgt_masterbarang where tahunbudget='".$thnBudget."' and kodebarang like '8%' and regional='".$regional."' order by kodebarang asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optbarang.="<option value=".$bar['kodebarang'].">".$bar['kodebarang']." - ".getNamaBrg($bar['kodebarang'])." (".getNamaBrg($bar['kodebarang'],'satuan').")</option>";
		}


		echo $hkEfektip."###".$optWs."###".$optKdbdgt."###".$optbarang;
	break;
	case'gethargabaranglain':
		$str = "select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kodeOrg,0,4)."'";
		$res = fetchdata($str);
		$regional = $res[0]['regional'];
		
		$str="select hargasatuan from ".$dbname.".bgt_masterbarang where tahunbudget='".$thnBudget."' and kodebarang = '".$param['kodebarang']."' and regional='".$regional."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optbarang=$bar['hargasatuan'];
		}
		
		echo $optbarang;
	break;
	case'getUpah':
		if($kdGol==''){
		   exit("Error: Budget code required");
		}
		$str="select jumlah from ".$dbname.".bgt_upah where tahunbudget='".$thnBudget."' and kodeorg='".substr($kodeOrg,0,4)."' and golongan='".$kdGol."' and closed=1";
		$res = fetchdata($str);
		if(count($res)>0){
			if($res[0]['jumlah']=='0'){
				exit("Error : Upah rata - rata belum ada atau belum di tutup.");
			}else{
				$totalUpah=(floatval($res[0]['jumlah'])*floatval($jmlhPerson))*floatval($hkEfektif);
				echo number_format($totalUpah);
			}
		}else{
		  exit("Error: Data not closed, please re-check");
		}

	break;
	case'saveSdm':
		cekheader($param);
		if($kdGol==''||$jmlhPerson==''||$totBiaya==0){
			exit("Error: Jenis, Jumlah TK dan Total Biaya tidak boleh kosong.");
		}
		$vol=floatval($jmlhPerson)*floatval($hkEfektif);
		
		if($param['method']=='update'){
			$data = array(
				'tahunbudget'=> $thnBudget,
				'kodeorg'    => $kodeOrg,
				'tipebudget' => $tipeBudget,
				'kodebudget' => $kdGol,
				'kodevhc'    => $kdVhc,
				'rupiah'     => $totBiaya,
				'jumlah'     => $jmlhPerson,
				'satuanj'    => 'orang',
				'updateby'   => $_SESSION['standard']['userid'],
				'volume'     => $vol,
				'satuanv'    => 'HK'
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$where = "kunci='".$param['index']."'";
			$str = updateQuery($dbname,'bgt_budget',$data,$where);
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}else{			
			$str="select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and kodebudget='".$kdGol."' and kodevhc='".$kdVhc."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Error : Data already exist");
			}
			
			
			$str="insert into ".$dbname.".bgt_budget (tahunbudget, kodeorg, tipebudget, kodebudget,kodevhc,rupiah,jumlah, satuanj,updateby,volume, satuanv) 
			values('".$thnBudget."','".$kodeOrg."','".$tipeBudget."','".$kdGol."','".$kdVhc."','".$totBiaya."','".$jmlhPerson."','orang','".$_SESSION['standard']['userid']."','".$vol."','HK')";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
		
		
	break;
	case'saveMat':
		cekheader($param);
		if($kdBudget==''||$kdBrg==''||$totHarga==0||$jmlhBrg==''){
			exit("Error : Kode Barang, Jumlah dan Rupiah harus terisi.");
		}
		if(getNamaBrg($kdBrg)==''){
			exit("Error : Kode barang tidak terdaftar.");
		}
		
		$str = "select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kodeOrg,0,4)."'";
		$res = fetchdata($str);
		$regional = $res[0]['regional'];
		
		if($param['method']=='update'){
			$data = array(
				'tahunbudget'=> $thnBudget,
				'kodeorg'    => $kodeOrg,
				'tipebudget' => $tipeBudget,
				'kodebudget' => $kdBudget,
				'kodevhc'    => $kdVhc,
				'rupiah'     => $totHarga,
				'kodebarang' => $kdBrg,
				'regional'   => $regional,
				'updateby'   => $_SESSION['standard']['userid'],
				'jumlah'     => $jmlhBrg,
				'satuanj'    => $satuanBrg
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$where = "kunci='".$param['index']."'";
			$str = updateQuery($dbname,'bgt_budget',$data,$where);
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}else{			
			$str = "select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and kodebudget='".$kdBudget."' and kodebarang='".$kdBrg."' and kodevhc='".$kdVhc."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Error: Data already exist");
			}
			
			$str="insert into ".$dbname.".bgt_budget (tahunbudget, kodeorg, tipebudget, kodebudget,kodevhc,rupiah, kodebarang, regional, updateby,jumlah,satuanj) 
			values('".$thnBudget."','".$kodeOrg."','".$tipeBudget."','".$kdBudget."','".$kdVhc."','".$totHarga."','".$kdBrg."','".$regional."','".$_SESSION['standard']['userid']."','".$jmlhBrg."','".$satuanBrg."')";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
		
	break;
	case'saveService':
		cekheader($param);
		if($kdBudgetS==''||$totHargaJam==0||$jmlhJam==''||$jmlhJam==''||$jmlhJam=='0'){
			exit("Error : Kode anggaran, Kode Workshop, Jumlah Jam dan Total Biaya tidak boleh kosong. \nJika total biaya nol, mohon input alokasi jam bengkel");
		}
		
		if($param['method']=='update'){
			$data = array(
				'tahunbudget'=> $thnBudget,
				'kodeorg'    => $kodeOrg,
				'tipebudget' => $tipeBudget,
				'kodebudget' => $kdBudgetS,
				'kodews'     => $param['kdWorkshop'],
				'kodevhc'    => $kdVhc,
				'rupiah'     => $totHargaJam,
				'jumlah'     => $jmlhJam,
				'satuanj'    => 'JAM',
				'updateby'   => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$where = "kunci='".$param['index']."'";
			$str = updateQuery($dbname,'bgt_budget',$data,$where);#exit("error".$str);
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}else{
			$str="select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and kodebudget='".$kdBudgetS."' and kodevhc='".$kdVhc."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Error: Data already exist");
			}
			$str="insert into ".$dbname.".bgt_budget (tahunbudget, kodeorg, tipebudget, kodebudget,kodews,kodevhc, rupiah,jumlah,satuanj,updateby) 
			values('".$thnBudget."','".$kodeOrg."','".$tipeBudget."','".$kdBudgetS."','".$param['kdWorkshop']."','".$kdVhc."','".$totHargaJam."','".$jmlhJam."','JAM','".$_SESSION['standard']['userid']."')";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	case'saveLain':
		cekheader($param);
		if($kdBudgetB==''||$totBiayaB==0||$noAkun==''){
			exit("Error : Kode Anggaran, Jenis Biaya dan Total Biaya harus terisi.");
		}
		
		if($param['method']=='update'){
			$data = array(
				'tahunbudget' => $thnBudget,
				'kodeorg'     => $kodeOrg,
				'tipebudget'  => $tipeBudget,
				'kodebudget'  => $kdBudgetB,
				'kodebarang'  => $param['kodebarang'],
				'jumlah'      => $param['kuantitas'],
				'satuanj'     => getNamaBrg($param['kodebarang'],'satuan'),
				'kodevhc'     => $kdVhc,
				'noakun'      => $noAkun,
				'rupiah'      => $totBiayaB,
				'updateby'    => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}
			$where = "kunci='".$param['index']."'";
			$str = updateQuery($dbname,'bgt_budget',$data,$where);#exit("error".$str);
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}else{
			$str = "select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and kodebudget='".$kdBudgetB."' and noakun='".$noAkun."' and kodevhc='".$kdVhc."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Error: Data already exist");
			}
			$str="insert into ".$dbname.".bgt_budget (tahunbudget, kodeorg, tipebudget, kodebudget,kodevhc, noakun,rupiah,updateby,kodebarang,jumlah,satuanj) 
			values('".$thnBudget."','".$kodeOrg."','".$tipeBudget."','".$kdBudgetB."','".$kdVhc."','".$noAkun."','".$totBiayaB."','".$_SESSION['standard']['userid']."','".$param['kodebarang']."','".$param['kuantitas']."','".getNamaBrg($param['kodebarang'],'satuan')."')";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	
    case'loadDataSdm':
		if($param['jenis']=='popup'){
			$tab.="<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddatasdm');\" >";
		}
		$tab.="
		<table id=loaddatasdm cellpadding=3 cellspacing=1 border=0 class=sortable>
				<thead>
				<tr class=rowheader>
				<th align=center width=30px>No</th>
				<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center>".$_SESSION['lang']['tipeBudget']."</th>
				<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['kodevhc']."</th>
				<th align=center>".$_SESSION['lang']['volume']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['rp']."</th>
				<th align=center colspan=2>Action</th>
				</tr>
				</thead>
			<tbody>";
				
		$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($kodeOrg,0,4)."'");
		if($tipeorg[substr($kodeOrg,0,4)]=='PABRIK'){
			$whsdm=" and kodebudget like 'EXPL%'";
		}elseif($tipeorg[substr($kodeOrg,0,4)]=='BULKING'){
			$whsdm=" and kodebudget like 'EXPLBULK%'";
		}else{
			$whsdm=" and kodebudget like 'SDM%'";
		}
		
        $str="select tutup,kunci,tahunbudget, kodeorg, tipebudget, kodebudget,kodevhc,rupiah,jumlah, satuanj,volume, satuanv from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and kodevhc='".$kdVhc."' ".$whsdm."";
        $res = fetchdata($str);
		foreach($res as $bar){
            $no+=1;
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$nmnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar['kodevhc']."'");
			
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align='center' width=50px>".$bar['tahunbudget']."</td>";
            $tab.="<td align='left'>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align='center'>".$bar['tipebudget']."</td>";
            $tab.="<td align='left'>".$bar['kodebudget']." - ".$nmkode[$bar['kodebudget']]."</td>";
			if($nmnopol[$bar['kodevhc']]!=''){				
				$tab.="<td align='left'>".$bar['kodevhc']." - ".$nmnopol[$bar['kodevhc']]."</td>";
			}else{
				$tab.="<td align='left'>".$bar['kodevhc']."</td>";
			}
            $tab.="<td align='right'>".@number_format($bar['volume'])."</td>";
            $tab.="<td align='center'>".$bar['satuanv']."</td>";
            $tab.="<td  align='right'>".$bar['jumlah']."</td>";
            $tab.="<td  align='center'>".$bar['satuanj']."</td>";
            $tab.="<td align='right'>".@number_format($bar['rupiah'])."</td>";
			
			if($bar['tutup']==0 and $param['jenis']!='popup'){
				$tab.="<td align=center style='cursor:pointer;width:25px'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editsdm('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['volume']."','".$bar['jumlah']."','".$bar['rupiah']."');\" ></td>";
				
				$tab.="<td align=center style='cursor:pointer;width:25px'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$bar['kunci'].",1)\" src='images/application/application_delete.png'/></td>";
			}else{					
				$tab.="<td align=center>&nbsp;</td>";
				$tab.="<td align=center>&nbsp;</td>";
			}
            $tab.="</tr>";
			$thk+=$bar['volume'];
			$ttk+=$bar['jumlah'];
			$trp+=$bar['rupiah'];
        }
		$tab.="<tr class=rowcontent>
				<td align=center colspan=6>TOTAL</td>
				<td align=right>".number_format($thk)."</td>
				<td></td>
				<td align=right>".number_format($ttk)."</td>
				<td></td>
				<td align=right>".number_format($trp)."</td>
				<td></td>
				<td></td>
				</tr>";
		$tab.="</tbody></table>";		
        echo $tab;
    break;
    case'getBarang':
		$tab="
				<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>
				<thead>
				<tr class=rowheader>
				<th align=center>No.</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['namabarang']."</th>
				<th align=center>".$_SESSION['lang']['satuan']."</th>
				<th align=center>".$_SESSION['lang']['harga']."</th>
				</tr><tbody>
				";
		if($nmBrg==''){
			@$nmBrg=$kdBarang;
		}
		
		
		$str="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kdTraksi,0,4)."' ";
		$res = fetchdata($str);
		$region = $res[0]['regional'];
		if($klmpkBrg!=''){
			$whrd.=" and left(kodebarang,3)='".substr($klmpkBrg,2,3)."'";
		}
		if($nmBrg!=''){
			$whrd.=" and kodebarang in (select kodebarang from ".$dbname.".log_5masterbarang where kodebarang like '%".$nmBrg."%' or namabarang like '%".$nmBrg."%' )";
		}
		$no=0;
		$str="select * from ".$dbname.".bgt_masterbarang where regional='".$region."' and tahunbudget='".$thnBudget."' and closed=1 ".$whrd." ";
		$rData=fetchData($str);
		foreach($rData as $res){
			$sDt="select namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";
			$nm=fetchData($sDt)[0];
			
			$no+=1;
			$tab.="<tr class=rowcontent style=cursor:pointer onclick=\"setData('".$res['kodebarang']."','".$nm['namabarang']."','".$nm['satuan']."','".@number_format($res['hargasatuan'])."')\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$res['kodebarang']."</td>";
			$tab.="<td>".$nm['namabarang']."</td>";
			$tab.="<td>".$nm['satuan']."</td>";
			$tab.="<td align=right>".@number_format($res['hargasatuan'])."</td>";
			$tab.="</tr>";
		}
		$tab.="</tbody>";
		$tab.="</table>";

		echo $tab;
	break;
	case'getHarga':
		if(($jmlhBrg=='')||($jmlhBrg=='0')){
			exit("Material volume is empty");
		}
		
		$str = "select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".substr($kodeOrg,0,4)."' ";
		$res = fetchdata($str);
		
		$str = "select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$res[0]['regional']."' and kodebarang='".$kdBrg."' and tahunbudget='".$thnBudget."' and closed=1";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("Error: Harga barang budget belum ada."); 
		}

		$hasil = floatval($res[0]['hargasatuan'])*floatval($jmlhBrg);
		echo number_format($hasil);
	break;
	case'getBiayaService':
		if(($kdBudgetS=='')||($kdWorkshop=='')||($jmlhJam=='')||($jmlhJam=='0')){
			exit("Warning : Kode anggaran, Kode wordshop, Jam Harus terisi.");
		}
	 
		$str = "select distinct rpperjam from ".$dbname.".bgt_biaya_ws_per_jam where tahunbudget='".$thnBudget."' and kodews='".$kdWorkshop."'";
		$res = fetchdata($str);
		if(count($res)==0){
			exit("Warning : Budget workshop belum ada, silahkan input terlebih dahulu : \nAnggaran - Transaksi - Traksi - Total Jam Bengkel.\nAnggaran - Transaksi - Traksi - Budget Biaya Bengkel.");
		}

		$hasil = floatval($res[0]['rpperjam'])*floatval($jmlhJam);
		echo number_format($hasil);
	break;
	case'delData':
		$sDel="delete from ".$dbname.".bgt_budget where kunci='".$idData."'";
		try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'loadDataMat':
		if($param['jenis']=='popup'){
			$tab.="<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddatamat');\" >";
		}
		$tab.="
		<table id=loaddatamat cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center width=30px>No</th>
            <th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
            <th align=center>".$_SESSION['lang']['kodeorg']."</th>
            <th align=center>".$_SESSION['lang']['tipeBudget']."</th>
            <th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
            <th align=center>".$_SESSION['lang']['kodevhc']."</th>
            <th align=center>".$_SESSION['lang']['kodebarang']."</th>
            <th align=center>".$_SESSION['lang']['namabarang']."</th>
            <th align=center>".$_SESSION['lang']['jumlah']."</th>
            <th align=center>".$_SESSION['lang']['satuan']."</th>
            <th align=center>".$_SESSION['lang']['rp']."</th>
            <th align=center colspan=2>Action</th>
            </tr>
            </thead><tbody>
		";	
		$str="select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and substring(kodebudget,1,1)='M' and kodevhc='".$kdVhc."'";
		$res = fetchdata($str);$no=0;
		foreach($res as $bar){
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$nmnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar['kodevhc']."'");
			
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align='center'>".$bar['tahunbudget']."</td>";
			$tab.="<td align='center'>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td align='center'>".$bar['tipebudget']."</td>";
			$tab.="<td align='left'>".$bar['kodebudget']." - ".$nmkode[$bar['kodebudget']]."</td>";
			if($nmnopol[$bar['kodevhc']]!=''){				
				$tab.="<td align='left'>".$bar['kodevhc']." - ".$nmnopol[$bar['kodevhc']]."</td>";
			}else{
				$tab.="<td align='left'>".$bar['kodevhc']."</td>";
			}
			
			$tab.="<td align='right'>".$bar['kodebarang']."</td>";
			$tab.="<td align='left'>".$optNmBrg[$bar['kodebarang']]."</td>";
			$tab.="<td  align='right'>".number_format($bar['jumlah'])."</td>";
			$tab.="<td  align='center'>".$bar['satuanj']."</td>";
			$tab.="<td align='right'>".number_format($bar['rupiah'])."</td>";
			
			if($bar['tutup']==0 and $param['jenis']!='popup'){
				$tab.="<td align=center style='width:25px'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editmat('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['kodebarang']."','".$optNmBrg[$bar['kodebarang']]."','".$bar['satuanj']."','".$bar['jumlah']."','".$bar['rupiah']."');\" ></td>";
				
				$tab.="<td align=center style='cursor:pointer;width:25px'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$bar['kunci'].",2)\" src='images/application/application_delete.png'/></td>";
			}else{					
				$tab.="<td align=center>&nbsp;</td>";
				$tab.="<td align=center>&nbsp;</td>";
			}
            $tab.="</tr>";
			$thk+=$bar['volume'];
			$ttk+=$bar['jumlah'];
			$trp+=$bar['rupiah'];
        }
		$tab.="<tr class=rowcontent>
				<td align=center colspan=10>TOTAL</td>
				<td align=right>".number_format($trp)."</td>
				<td></td>
				<td></td>
				</tr>";
		$tab.="</tbody></table>";	
		echo $tab;
	break;
	case'loadDtService':
		if($param['jenis']=='popup'){
			$tab.="<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddataser');\" >";
		}
		$tab.="
		<table id=loaddataser cellpadding=3 cellspacing=1 border=0 class=sortable>
					<thead>
					<tr class=rowheader>
					<th align=center width=30px>No</th>
					<th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
					<th align=center>".$_SESSION['lang']['kodeorg']."</th>
					<th align=center>".$_SESSION['lang']['tipeBudget']."</th>
					<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
					<th align=center>".$_SESSION['lang']['kodevhc']."</th>
					<th align=center>".$_SESSION['lang']['kdWorks']."</th>
					<th align=center>".$_SESSION['lang']['jumlah']."</th>
					<th align=center>".$_SESSION['lang']['satuan']."</th>
					<th align=center>".$_SESSION['lang']['rp']."</th>
					<th align=center colspan=2>Action</th>
					</tr>
				</thead>
				<tbody>";
			
		$str="select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and kodebudget like '%SERVICE%' and kodevhc='".$kdVhc."'";
		$res = fetchdata($str);$no=0;
		foreach($res as $bar){
            $no+=1;
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."' or kodeorganisasi='".$bar['kodews']."'");
			$nmnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar['kodevhc']."'");
			
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align='center'>".$bar['tahunbudget']."</td>";
			$tab.="<td align='center'>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align='center'>".$bar['tipebudget']."</td>";
            $tab.="<td align='left'>".$bar['kodebudget']." - ".$nmkode[$bar['kodebudget']]."</td>";
            if($nmnopol[$bar['kodevhc']]!=''){				
				$tab.="<td align='left'>".$bar['kodevhc']." - ".$nmnopol[$bar['kodevhc']]."</td>";
			}else{
				$tab.="<td align='left'>".$bar['kodevhc']."</td>";
			}
            $tab.="<td align='left'>".$bar['kodews']." - ".$nmorg[$bar['kodews']]."</td>";
            $tab.="<td align='center'>".$bar['jumlah']."</td>";
            $tab.="<td align='center'>".$bar['satuanj']."</td>";
            $tab.="<td align='right'>".number_format($bar['rupiah'])."</td>";
			
			if($bar['tutup']==0 and $param['jenis']!='popup'){
				$tab.="<td align=center style='width:25px'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editsrv('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['kodews']."','".$bar['jumlah']."','".$bar['rupiah']."');\" ></td>";
				
				$tab.="<td align=center style='width:25px'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$bar['kunci'].",3)\" src='images/application/application_delete.png'/></td>";
			}else{					
				$tab.="<td align=center>&nbsp;</td>";
				$tab.="<td align=center>&nbsp;</td>";
			}
            $tab.="</tr>";
			$thk+=$bar['volume'];
			$ttk+=$bar['jumlah'];
			$trp+=$bar['rupiah'];
			
            $tab.="</tr>";
        }
		$tab.="<tr class=rowcontent>
				<td align=center colspan=9>TOTAL</td>
				<td align=right>".number_format($trp)."</td>
				<td></td>
				<td></td>
				</tr>";
        $tab.="</tbody></table>";	
		echo $tab;
	break;
	case'loadDtLain':
		if($param['jenis']=='popup'){
			$tab.="<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddataoth');\" >";
		}
		$tab.="
		<table id=loaddataoth cellpadding=3 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <th align=center width=30px>No</th>
            <th align=center width=50px>".$_SESSION['lang']['budgetyear']."</th>
            <th align=center>".$_SESSION['lang']['kodeorg']."</th>
            <th align=center>".$_SESSION['lang']['tipeBudget']."</th>
            <th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
            <th align=center>".$_SESSION['lang']['kodevhc']."</th>
            <th align=center>".$_SESSION['lang']['namabarang']."</th>
            <th align=center>".$_SESSION['lang']['noakun']."</th>
            <th align=center>".$_SESSION['lang']['namaakun']."</th>
            <th align=center>".$_SESSION['lang']['rp']."</th>
            <th align=center colspan=2>Action</th>
            </tr>
		</thead>
		<tbody>";
		
		$str="select * from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kodeOrg."' and tipebudget='".$tipeBudget."' and kodebudget like '%TRANSIT%' and kodevhc='".$kdVhc."'";
		$res = fetchdata($str);$no=0;
		foreach($res as $bar){
            $no+=1;
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$nmnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar['kodevhc']."'");

            $tab.="<tr class=rowcontent>";
            $tab.="<td align='center'>".$no."</td>";
            $tab.="<td align='center'>".$bar['tahunbudget']."</td>";
			$tab.="<td align='center'>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align='center'>".$bar['tipebudget']."</td>";
            $tab.="<td align='left'>".$bar['kodebudget']." - ".$nmkode[$bar['kodebudget']]."</td>";
            if($nmnopol[$bar['kodevhc']]!=''){				
				$tab.="<td align='left'>".$bar['kodevhc']." - ".$nmnopol[$bar['kodevhc']]."</td>";
			}else{
				$tab.="<td align='left'>".$bar['kodevhc']."</td>";
			}
			
            $tab.="<td align='left'>".$bar['kodebarang']." - ".getNamaBrg($bar['kodebarang'])."</td>";
            $tab.="<td align='right'>".$bar['noakun']."</td>";
            $tab.="<td align='left'>".$optNmAkun[$bar['noakun']]."</td>";
            $tab.="<td align='right'>".number_format($bar['rupiah'])."</td>";
			if($bar['tutup']==0 and $param['jenis']!='popup'){
				$tab.="<td align=center style='width:25px'><img src=images/application/application_edit.png class=zImgBtn	title='Edit' onclick=\"editoth('".$bar['kunci']."','".$bar['kodebudget']."','".$bar['noakun']."','".$bar['rupiah']."','".$bar['kodebarang']."','".$bar['jumlah']."');\" ></td>";
				
				$tab.="<td align=center style='width:25px'><img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteSdm(".$bar['kunci'].",4)\" src='images/application/application_delete.png'/></td>";
			}else{					
				$tab.="<td align=center>&nbsp;</td>";
				$tab.="<td align=center>&nbsp;</td>";
			}
			
			$tab.="</tr>";
			$thk+=$bar['volume'];
			$ttk+=$bar['jumlah'];
			$trp+=$bar['rupiah'];
        }
        $tab.="<tr class=rowcontent>
				<td align=center colspan=9>TOTAL</td>
				<td align=right>".number_format($trp)."</td>
				<td></td>
				<td></td>
				</tr>";
        $tab.="</tbody></table>";	
		echo $tab;
	break;
	case'setKdBrg':
		echo substr($klmpkBrg,2,3);
	break;
	case'loaddata':
		$sJm="select * from ".$dbname.".bgt_biaya_ken_per_jam order by tahunbudget desc";
		$res = fetchdata($sJm);
		foreach($res as $rJm){
			$rJmthn[$rJm['tahunbudget']][$rJm['kodetraksi']][$rJm['kodevhc']]=$rJm['rpsetahun'];
			$rJmhm[$rJm['tahunbudget']][$rJm['kodetraksi']][$rJm['kodevhc']]=$rJm['rpperjam'];
			$ttlJmhm[$rJm['tahunbudget']][$rJm['kodetraksi']][$rJm['kodevhc']]=$rJm['jamsetahun'];
		}
		
		$tab = "";
		$limit= 20;
		$page = 0;
		$param['page'] = isset($param['page']) ? $param['page'] : '0';
		if (isset($param['page'])) {$page = intval($param['page']);if ($page < 0){$page = 0;}}
		
		$offset    = floatval($page) * $limit;
		$maxdisplay= floatval($page * $limit);
		$no        = $maxdisplay;
		$colspan   = 18;
		
		
		$where="";
		if($thnBudget!=''){
			$where.=" and tahunbudget='".$thnBudget."'";
		}
		if($kdVhc!=''){
			$where.=" and kodevhc like '%".$kdVhc."%'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeorg like '".$param['kodeorg']."%'";
		}
		if($param['kodetrk']!=''){
			$where.=" and kodeorg like '".$param['kodetrk']."%'";
		}

		$sql="select * from ".$dbname.".bgt_budget where substr(kodeorg,1,4) in (".getOrgDetail(2).") and tipebudget='TRK' ".$where." group by tahunbudget,kodeorg,tipebudget, kodevhc order by tahunbudget desc  ";
		$res = fetchdata($sql);
		$jlhbrs = count($res);
		
		$str="select kunci,tahunbudget, kodeorg, tipebudget, kodebudget,kodevhc,tutup from ".$dbname.".bgt_budget where substr(kodeorg,1,4) in (".getOrgDetail(2).") and tipebudget='TRK' ".$where."  group by tahunbudget,kodeorg,tipebudget, kodevhc order by tahunbudget desc  limit ".$offset.",".$limit."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$nmorg  =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$tipeorg=makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($bar['kodeorg'],0,4)."'");
			
			if($tipeorg[substr($bar['kodeorg'],0,4)]=='PABRIK'){
				$whsdm=" and kodebudget like 'EXPL%'";
			}elseif($tipeorg[substr($bar['kodeorg'],0,4)]=='BULKING'){
				$whsdm=" and kodebudget like 'EXPLBULK%'";
			}else{
				$whsdm=" and kodebudget like 'SDM%'";
			}
			
			$tab.="<tr class=rowcontent style=height:20px>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".$bar['tahunbudget']."</td>";
			$tab.="<td align=center>".$bar['tipebudget']."</td>";
			$tab.="<td>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
			$tab.="<td>".$bar['kodevhc']."</td>";
			if(getNopol($bar['kodevhc'])!=''){					
				$tab.="<td>".getNopol($bar['kodevhc'])."</td>";
			}else{
				$tab.="<td>".getNopol($bar['kodevhc'],'d')."</td>";
			}
			$tab.="<td align=right>".@number_format($ttlJmhm[$bar['tahunbudget']][$bar['kodeorg']][$bar['kodevhc']])."</td>";
			
			$getdt="'".$bar['tipebudget']."','".$bar['tahunbudget']."','".$bar['kodeorg']."','".$bar['kodevhc']."'";
			
			#SDM
			$sdm = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and kodeorg='".$bar['kodeorg']."' and tipebudget='".$bar['tipebudget']."' and kodevhc = '".$bar['kodevhc']."' ".$whsdm."";
			$ressdm = fetchData($sdm);
			$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('sdm',".$getdt.")>".@number_format($ressdm[0]['jumlah'])."</td>";
			
			#Material
			$mat = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and kodeorg='".$bar['kodeorg']."' and tipebudget='".$bar['tipebudget']."' and kodebudget like 'M-%' and kodevhc = '".$bar['kodevhc']."'";
			$resmat = fetchData($mat);
			$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('mat',".$getdt.")>".@number_format($resmat[0]['jumlah'])."</td>";
			
			#SERVICE
			$tool = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and kodeorg='".$bar['kodeorg']."' and tipebudget='".$bar['tipebudget']."' and kodebudget like 'SERVICE%' and kodevhc = '".$bar['kodevhc']."'";
			$restool = fetchData($tool);
			$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('srv',".$getdt.")>".@number_format($restool[0]['jumlah'])."</td>";
			
			#TRANSIT
			$kont = "SELECT sum(rupiah) as jumlah  FROM " . $dbname . ".bgt_budget where tahunbudget='".$bar['tahunbudget']."' and kodeorg='".$bar['kodeorg']."' and tipebudget='".$bar['tipebudget']."' and kodebudget like 'TRANSIT%' and kodevhc = '".$bar['kodevhc']."'";
			$reskont = fetchData($kont);
			$tab.="<td align=right style='color:blue;cursor:pointer;' onclick=getdatadetail('oth',".$getdt.")>".@number_format($reskont[0]['jumlah'])."</td>";
			
			$ttlrp=$ressdm[0]['jumlah']+$resmat[0]['jumlah']+$restool[0]['jumlah']+$reskont[0]['jumlah'];
			$tab.="<td align=right hidden>".number_format($ttlrp)."</td>";
			$tab.="<td align=right  style='color:blue;cursor:pointer;' onclick=getdatadetail('rkp',".$getdt.")>".number_format($rJmthn[$bar['tahunbudget']][$bar['kodeorg']][$bar['kodevhc']],2)."</td>";
			$tab.="<td align=right>".number_format($rJmhm[$bar['tahunbudget']][$bar['kodeorg']][$bar['kodevhc']],2)."</td>";
			if($bar['tutup']==0){
				$tab.="<td align=center style='cursor:pointer;width:25px;'><img id='detail_edit' title='Edit' class=zImgBtn onclick=\"filFieldHead('".$bar['tahunbudget']."','".$bar['kodeorg']."','".$bar['kodevhc']."')\" src='images/application/application_edit.png'/></td>";
					
				$tab.="<td  align=center style='cursor:pointer;width:25px;'><img id='hapus' title='Delete' class=zImgBtn onclick=\"hapushead('".$bar['tahunbudget']."','".$bar['kodeorg']."','".$bar['kodevhc']."')\" src='images/application/application_delete.png'/></td>";
				
				$tab.="<td  align=center style='cursor:pointer;width:25px;'>
						<img id='detail_copy' title='Copy' class=zImgBtn onclick=\"viewOtherBlok('".$bar['tahunbudget']."','".$bar['kodevhc']."','".$bar['tipebudget']."','".@$bar['noakun']."','".@$bar['kodeorg']."','".@$bar['volume']."','".@$bar['satuanv']."','".@$bar['rotasi']."',event);\" src='images/application/application_cascade.png'/></td>";
						
				$tab.="<td align=center width=25px><img class=zImgBtn src=images/skyblue/posting.png onclick=\"posting('".$bar['tahunbudget']."','".$bar['kodeorg']."','".$bar['kodevhc']."');\" title='Close / Posting'></td>";	
			}else{
				$tab.="<td align=center width=25px></td>";
				$tab.="<td align=center width=25px></td>";
				$tab.="<td align=center width=25px></td>";
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unclose / Unposting";
					$unpost=" onclick=\"unposting('".$bar['tahunbudget']."','".$bar['kodeorg']."','".$bar['kodevhc']."');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Closed / Posted";
					$unpost='';
				}
				$tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn title='".$title."' ".$unpost." ></td>";
			}
			
			$tab.="<td align=center width=25px><img src=images/zoom.png class=zImgBtn class=zImgBtn height='30'  title='Preview'  onclick=\"getdatadetail('rkp',".$getdt.");\" ></td>";
			
			$tab.="</tr>";
		}
	  
	$foot=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
	
	echo $tab."####".$foot;
	
	break;
	case'form_otherblok':
		$theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }     
        $stream="";
        $no=0;
        $str="select * from ".$dbname.".vhc_5master where kodevhc = '".$kodeVhc."'";   
        $res=fetchdata($str);
        foreach($res as $bar){
			$jenisvhc=$bar['jenisvhc'];
			$nopol   =$bar['nopol'];
        }
       echo'<script language=JavaScript1.2 src=js/generic.js></script>
            <link rel=stylesheet type=text/css href=style/menu.css>
            <link rel=stylesheet type=text/css href=style/'.$gen.'>	
            <link rel=stylesheet type=text/css href=style/calendarblue.css>
            <script language="javascript" src="js/zMaster.js"></script>
            <script type="text/javascript" src="js/bgt_budget_kebun.js"></script>
            <link rel=stylesheet type=text/css href="style/zTable.css">
            ';   
        $stream.="<fieldset><legend>Copy From</legend>".$_SESSION['lang']['budgetyear'].": ".$tahunbudget;
        $stream.= "<br>".$_SESSION['lang']['kodevhc'].": ".$kodeVhc.", Nopol : ".$nopol.", Jenis Kend : ".$jenisvhc."";
        $stream.="</fieldset><hr>";

		$str="select distinct jenisvhc,namajenisvhc from ".$dbname.".vhc_5jenisvhc  order by jenisvhc asc";
		$res = fetchdata($str);
		$opttt="<option value=''>".$_SESSION['lang']['all']."</option>";
		foreach($res as $rOrg2){
			$n="";
			if($jenisvhc==$rOrg2['jenisvhc']){
				$n="selected";
			}
			$opttt.="<option value=".$rOrg2['jenisvhc']." ".$n.">".$rOrg2['jenisvhc']." - ".$rOrg2['namajenisvhc']."</option>";
		}
		
		$optdiv="<option value=''>".$_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorg."%' and tipe='TRAKSI'";
		$res = fetchdata($str);
		foreach($res as $rOrg2){
			$optdiv.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['kodeorganisasi']." - ".$rOrg2['namaorganisasi']."</option>";
		}
		
		$e="isiviewOtherBlok('".$tahunbudget."','".$kodeVhc."','".$tipebudget."','".$noakun."','".$kodeorg."','".$volume."','".$satuanvolume."','".$rotasi."','event','filter');";
		
		$stream.="<fieldset><legend>Copy to</legend>Jenis Kend : <select style='width:155px;' id='ttcopyallblok' onchange=\"".$e."\">".$opttt."</select><select style='width:155px;' hidden id='divisicopyallblok' >".$optdiv."</select>
		<button class=mybutton onclick=\"".$e."\" >".$_SESSION['lang']['preview']."</button>
					
		";
		$stream.="<table class=sortable border=0 cellpadding=5 cellspacing=1 width=100%>";
        $stream.="<thead>
        <tr class=rowtitle>
            <th align=center>No</th>
            <th align=center>".$_SESSION['lang']['jenis']."</th>
            <th align=center>".$_SESSION['lang']['kodevhc']."</th>
            <th align=center>".$_SESSION['lang']['nopol']."</th>
            <th align=center>".$_SESSION['lang']['detail']."</th>
            <th align=center>Copy</th>";
        $stream.="</tr>
        </thead>
        <tbody id=containerdx></fieldset>";
		
		echo $stream;
	break;
    case'otherblok':
        $no=0;
		$str="select * from ".$dbname.".vhc_5master where kodevhc = '".$kodeVhc."'";   
        $res=fetchdata($str);
        foreach($res as $bar){
			$jenisvhc=$bar['jenisvhc'];
			$nopol   =$bar['nopol'];
        }
		
		$wherestatus="";
		$whtt='';
		if($jeniskend!=''){
			$whtt.=" and jenisvhc ='".$jeniskend."' ";
		}
		$whtt.=" and kodetraksi like '".substr($param['kodeorg'],0,4)."%' ";
		
        $str="select * from ".$dbname.".vhc_5master where 1=1 ".$whtt." and kodevhc not in (select kodevhc from ".$dbname.".bgt_budget where tahunbudget = '".$tahunbudget."' and tipebudget = '".$tipebudget."') and kodevhc in (select kodevhc from ".$dbname.".bgt_vhc_jam_vw where tahunbudget = '".$tahunbudget."') order by kodevhc";   
        $res=fetchdata($str);
        $no=0;
        foreach($res as $bar){
            $no+=1;
            $stream.="<tr class=rowcontent id=row".$no.">
               <td align=center>".$no."</td>
               <td align=center>".$bar['jenisvhc']."</td>
               <td align=left id=kdvhccopy".$no.">".$bar['kodevhc']."</td>
               <td align=left>".$bar['nopol']."</td>
               <td align=left>".$bar['detailvhc']."</td>";
            $stream.="<td align=center><input type=\"checkbox\" name=\"copy\" value=\"copy\" onclick=\"copybudget('".$no."','0','".$tahunbudget."','".$kodeVhc."','TRK');\"></td>";               
              $stream.="</tr>";
        }
		$stream.="<tr><td colspan=6 align=right>
						<button class='mybutton' onclick=\"copybudgetall('".$no."','".$tahunbudget."','".$kodeVhc."','TRK');\">Copy All</button>
		</td></tr>";
        $stream.="</tbody></table>";
		echo $stream;
    break;
	case'hapushead':
		$sdel = "delete from " . $dbname . ".bgt_budget where  tipebudget='TRK' and tahunbudget='".$thnbdget."' and kodeorg='".$kdtrk."' and kodevhc='".$kdVhc."'
		"; #exit("error".$sdel);
		try{$owlPDO->exec($sdel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'posting':
		try{
		$owlPDO->beginTransaction();
			
			$str="select * from ".$dbname.".bgt_biaya_ken_per_jam where tahunbudget='".$param['tahunbudget']."' and kodevhc='".$param['kodevhc']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$ttlbiaya+=$bar['rpsetahun'];
				$jamsetahun+=$bar['jamsetahun'];
				$rpperjam=$bar['rpperjam'];
			}
			
			if($jamsetahun==0){
				throw new PDOException("HM / KM kendaraan belum ada belum ada.");
			}
			
			#delete sebaran
			$str = "delete from " . $dbname . ".bgt_distribusi where kunci in (select kunci from " . $dbname . ".bgt_budget where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='TRK' and kodevhc='".$param['kodevhc']."')"; 
			$owlPDO->exec($str);
			
			
			$str="select * from ".$dbname.".bgt_vhc_jam where tahunbudget='".$param['tahunbudget']."' and kodevhc='".$param['kodevhc']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				for($i=1;$i<=12;$i++){
					$persen[$i]+=$bar["jam".addZero($i,2)];
					$ttlpersen+=$bar["jam".addZero($i,2)];
				}
			}
			
			$str = "select * from " . $dbname . ".bgt_budget where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='TRK' and kodevhc='".$param['kodevhc']."'"; #exit("error".$str);
			$res = fetchdata($str);
			foreach($res as $bar){
				$str="insert into ".$dbname.".bgt_distribusi (`kunci`";
				for($i=1;$i<=12;$i++){
					$str.=",`rp".addZero($i,2)."`";
					$str.=",`fis".addZero($i,2)."`";
				}
				$str.=") values('".$bar['kunci']."'";
				for($i=1;$i<=12;$i++){
					$str.=",'".$persen[$i]/$ttlpersen*$bar['rupiah']."'";
					$str.=",'".$persen[$i]/$ttlpersen*$bar['jumlah']."'";
				}
				$str.=");";
				$owlPDO->exec($str);
				
			}
			
			
			#update transaksi
			$rupiahvhc=0;
			$str = "select * from " . $dbname . ".bgt_budget where tahunbudget = '".$param['tahunbudget']."' and tipebudget!='TRK' and kodevhc='".$param['kodevhc']."' and pta='BGT'"; #exit("error".$str);
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['tutup']==1){
					//throw new PDOException("Budget Alokasi HM / KM kendaraan sudah ditutup.");
				}
				$rupiahvhc=round($bar['jumlah']*$rpperjam,0);
				if($rpperjam>0){					
					$str = "update " . $dbname . ".bgt_budget set rupiah='".$rupiahvhc."' where kunci = '".$bar['kunci']."'"; #exit("error".$str);
					$owlPDO->exec($str);
				}
			}
			
			
			$str = "update " . $dbname . ".bgt_budget set tutup='1' where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='TRK' and kodevhc='".$param['kodevhc']."'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$str="update ".$dbname.".bgt_vhc_jam set tutup='1' where tahunbudget='".$param['tahunbudget']."'";
			$owlPDO->exec($str);
			
			$str = "update " . $dbname . ".bgt_budget set tutup='1' where tahunbudget = '".$param['tahunbudget']."' and kodeorg like '".substr($param['kodeorg'],0,4)."%' and tipebudget='WS'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	case'unposting':
		try{
		$owlPDO->beginTransaction();
			$str = "update " . $dbname . ".bgt_budget set tutup='0' where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='TRK' and kodevhc='".$param['kodevhc']."'"; #exit("error".$str);
			$owlPDO->exec($str);
			
			$str = "delete from " . $dbname . ".bgt_distribusi where kunci in (select kunci from " . $dbname . ".bgt_budget where tahunbudget = '".$param['tahunbudget']."' and kodeorg='".$param['kodeorg']."' and tipebudget='TRK' and kodevhc='".$param['kodevhc']."')"; 
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Error, ".addslashes($e->getMessage());
			die();
		}
	break;
	
	case'getThnBudget':
		$optThnTtp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$sThn="select distinct tahunbudget from ".$dbname.".bgt_budget where kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' and tipebudget='TRK' and tutup=0 order by tahunbudget desc";
		$qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
		$qThn->setFetchMode(PDO::FETCH_ASSOC);
		while($rThn=$qThn->fetch())
		{
			$optThnTtp.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
		}
		echo $optThnTtp;
	break;
	case'loaddatattlbiaya':
		$tab='';
		if($param['jenis']=='popup'){
			$tab.="<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportTableToExcel('loaddatattl');\" >";
		}
		$tab.="
		<table id=loaddatattl cellpadding=3 cellspacing=1 border=0 class=sortable>
			<thead>
			<tr class=rowheader>
				<th style='text-align:center' width=30px>No</th>
				<th style='text-align:center' width=50px>".$_SESSION['lang']['budgetyear']."</th>
				<th align=center>".$_SESSION['lang']['kodeorg']."</th>
				<th align=center>".$_SESSION['lang']['tipeBudget']."</th>
				<th align=center>".$_SESSION['lang']['kodeanggaran']."</th>
				<th align=center>".$_SESSION['lang']['kodevhc']."</th>
				<th align=center>".$_SESSION['lang']['kodebarang']."</th>
				<th align=center>".$_SESSION['lang']['noakun']."</th>
				<th align=center>".$_SESSION['lang']['namaakun']."</th>
				<th align=center>".$_SESSION['lang']['rp']."</th>
			</tr>
			</thead>
			<tbody>";
			
		$ttlbiaya=$jamsetahun=$rpperjam='0';
		
		$str="select * from ".$dbname.".bgt_biaya_ken_per_jam where tahunbudget='".$thnBudget."' and kodetraksi = '".$kdTraksi."' and kodevhc='".$kodeVhc."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$ttlbiaya+=$bar['rpsetahun'];
			$jamsetahun+=$bar['jamsetahun'];
			$rpperjam+=$bar['rpperjam'];
		}
		

		$str="select kodebarang, kunci,tahunbudget, kodeorg, tipebudget, kodebudget,kodevhc,rupiah,jumlah, satuanj,noakun from ".$dbname.".bgt_budget where tahunbudget='".$thnBudget."' and kodeorg='".$kdTraksi."' and tipebudget='TRK' and kodevhc='".$kodeVhc."'";
		$res = fetchdata($str);
		foreach($res as $bar){
            $no+=1;
			$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
			$nmnopol=makeOption($dbname,'vhc_5master','kodevhc,nopol',"kodevhc='".$bar['kodevhc']."'");
			
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align='center'>".$bar['tahunbudget']."</td>";
			$tab.="<td align='center'>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align='center'>".$bar['tipebudget']."</td>";
            $tab.="<td align='left'>".$bar['kodebudget']." - ".$nmkode[$bar['kodebudget']]."</td>";
			if($nmnopol[$bar['kodevhc']]!=''){				
				$tab.="<td align='left'>".$bar['kodevhc']." - ".$nmnopol[$bar['kodevhc']]."</td>";
			}else{
				$tab.="<td align='left'>".$bar['kodevhc']."</td>";
			}
			$tab.="<td align='left'>".$bar['kodebarang']." - ".getNamaBrg($bar['kodebarang'])."</td>";
			$tab.="<td align='right'>".$bar['noakun']."</td>";
            $tab.="<td align='left'>".@$optNmAkun[$bar['noakun']]."</td>";
            $tab.="<td align='right'>".number_format($bar['rupiah'])."</td>";
            $tab.="</tr>";
			
			@$ttl+=$bar['rupiah'];
        }
		
		
		$tab.="<tr class=rowcontent>
				<td style='text-align:right' colspan=9><b>TOTAL BIAYA</b></td>
				<td style='text-align:right'><b>".@number_format($ttlbiaya)."</b></td>
			</tr>";
		$tab.="<tr class=rowcontent>
				<td style='text-align:right' colspan=9><b>TOTAL JAM</b></td>
				<td style='text-align:right'><b>".@number_format($jamsetahun)."</b></td>
			</tr>";
		$tab.="<tr class=rowcontent>
				<td style='text-align:right' colspan=9><b>RUPIAH / JAM</b></td>
				<td style='text-align:right'><b>".@number_format($rpperjam)."</b></td>
			</tr>";
		$tab.="</tbody></table>";		
		echo $tab;
	break;
    default:
    break;
}

function cekheader($param){
	global $param;
	global $dbname;
	
	if($param['tipeBudget']==''){
		exit("Warning : Tipe Budget wajib diisi.");
	}
	if($param['thnBudget']==''){
		exit("Warning : Tahun budget wajib diisi.");
	}
	if(strlen($param['thnBudget'])<4){
		exit("Warning : Tahun budget salah.");
	}
	if($param['kdOrg']==''){
		exit("Warning : Kode organisasi wajib diisi.");
	}
	if($param['kdVhc']==''){
		exit("Warning : Kode kendaraan wajib diisi.");
	}
	$whr = " and kodevhc = '".$param['kdVhc']."' and tahunbudget='".$param['thnBudget']."'";
	$str="select * from ".$dbname.".bgt_vhc_jam where 1=1 ".$whr." and tutup='0'";
	$res=fetchdata($str);
	if(count($res)>0){
		exit("Warning : Budget jam kendaraan belum ditutup.");
	}

	$whr = " and tahunbudget='".$param['thnBudget']."' and tipebudget='".$param['tipeBudget']."' and kodeorg = '".$param['kdOrg']."' ";
	$str="select * from ".$dbname.".bgt_budget where 1=1 ".$whr." and tutup='1'";
	$res=fetchdata($str);
	if(count($res)>0){
		exit("Warning : Budget sudah ditutup.");
	}
}
?>