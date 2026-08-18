<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt = checkPostGet('pt','');
$ptscr = checkPostGet('ptscr','');
$unit = checkPostGet('unit','');
$unitsrc = checkPostGet('unitsrc','');
$kodebrg = checkPostGet('kodebrg','');
$kodebrgsrc = checkPostGet('kodebrgsrc','');
$tgl = tanggalsystem(checkPostGet('tgl',''));
$tglsrc = tanggalsystem(checkPostGet('tglsrc',''));
$tgl1 = checkPostGet('tgl','');
$nopo = checkPostGet('nopo','');
$harga=str_replace(',', '', checkPostGet('harga',''));
$hargaestimasi=str_replace(',', '', checkPostGet('hargaestimasi',''));
$time = date('Y-m-d H:i:s');
$txtfind = checkPostGet('txtfind','');
$method = checkPostGet('method','');

$pages = checkPostGet('page','');

switch ($method){
	case 'getunit':
		$optOrg="<option value=''>".$_SESSION['lang']['all']."</option>";
		if($pt!=''){
            $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."'";
            $res=fetchData($str);
            foreach ($res as $val) {
				if($val['kodeorganisasi']==$unit){
					$optOrg.="<option value='".$val['kodeorganisasi']."' selected>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";					
				}else{
					$optOrg.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
				}
            }
        }
        echo $optOrg;
    break;
	
	case'setBrg':
		$tampil = 0;
		$optnamaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)='4'");
		$str="select hargasatuan from ".$dbname.".log_5masterbarang where kodebarang='".$kodebrg."'";
		$res=fetchdata($str);
		$vhargasatuan=$res[0]['hargasatuan'];
		if($vhargasatuan!=''){
			$tampil=1;
			$optunit.="<option value=''>Global</option>";
			$exphargasatuan=explode(',',$vhargasatuan);
			foreach($exphargasatuan as $key){
				if($key==$unit){
					$optunit.="<option value='".$key."' selected>".$key." - ".$optnamaorg[$key]."</option>";					
				}else{
					$optunit.="<option value='".$key."'>".$key." - ".$optnamaorg[$key]."</option>";					
				}
			}
		}
		
		echo $tampil."##".$optunit;
	break;
	
	case'addfrompo':
		$tab="";
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
				<td align=center>" . $_SESSION['lang']['nopo'] . "</td>
				<td align=center>" . $_SESSION['lang']['harga'] . "</td>
			</tr>
			</thead>
			<tbody>";
			
		$str="select nopo, tanggal, hargasatuan from ".$dbname.".log_po_vw where kodebarang='".$kodebrg."' order by tanggal desc";
		$res=fetchdata($str);
		if(count($res)>0){
			$no=0;
			foreach($res as $val){
				$no++;
				$tab.="<tr class=rowcontent onclick=\"setpo('".$val['nopo']."','".hidezerodecimal($val['hargasatuan'],2)."','".tanggalnormal($val['tanggal'])."')\" style='cursor:pointer' title='Click'>
					<td align=center>".$no."</td>
					<td align=unit>".tanggalnormal($val['tanggal'])."</td>
					<td align=left>".$val['nopo']."</td>
					<td align=right>".hidezerodecimal($val['hargasatuan'],2)."</td>
				</tr>";
			}
		}else{
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}
		
		echo $tab;
	break;
	
	case 'getunitsrc':
		if(strlen($ptscr)<4){
			$optOrgsrc="<option value=''>".$_SESSION['lang']['all']."</option>";
            $sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$ptscr."'";
            $rOrg=fetchData($sOrg);
            foreach ($rOrg as $key => $val) {
                $optOrgsrc.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
            }
        }
        echo $optOrgsrc;

    
    break;

    case 'insert':
		try {
			$owlPDO->beginTransaction();
			
			if($kodebrg==''){
				throw new PDOException("Kode barang harus diisi.");
			}
			
			if($tgl==''){
				throw new PDOException("Tanggal harus diisi.");
			}
			
			if($harga=='' || $harga <= 0){
				throw new PDOException("Harga harus diisi dan lebih besar dari 0.");
			}
			
			if($unit!=''){
				$optkdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"length(kodeorganisasi)='4'");
				$pt=$optkdpt[$unit];
			}
			
			$str="select count(id) as jlhitem from ".$dbname.".log_5hargaterakhir where pt='".$pt."' and unit='".$unit."' and kodebarang='".$kodebrg."'";
			$res=fetchdata($str);
			$jml=$res[0]['jlhitem'];
			
			if($jml > 0){
				throw new PDOException("Item sudah pernah terdaftar disistem. Silahkan lakukan update data");
			}
			
			$str="insert into ".$dbname.".log_5hargaterakhir  (pt,unit,kodebarang,tanggal,hargasatuan,hargaestimasi,status,nopo,createdby,createtime,updateby,updatetime) values ('".$pt."','".$unit."','".$kodebrg."','".$tgl."','".$harga."','".$hargaestimasi."','1','".$nopo."','".$_SESSION['standard']['userid']."','".$time."','".$_SESSION['standard']['userid']."','".$time."')";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}
	break;

    case 'update':
		try {
			$owlPDO->beginTransaction();
			
			if($kodebrg==''){
				throw new PDOException("Kode barang harus diisi.");
			}
			
			if($tgl==''){
				throw new PDOException("Tanggal harus diisi.");
			}
			
			if($harga=='' || $harga <= 0){
				throw new PDOException("Harga harus diisi dan lebih besar dari 0.");
			}
			
			if($unit!=''){
				$optkdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk',"length(kodeorganisasi)='4'");
				$pt=$optkdpt[$unit];
			}
			
			$str="select * from ".$dbname.".log_5hargaterakhir where pt='".$pt."' and unit='".$unit."' and kodebarang='".$kodebrg."' and status='1'";
			$res=fetchdata($str);
			$tgllalu = $res[0]['tanggal'];
			$tglskrg = $tgl;
			$selisitgl = selisitgl($tglskrg,$tgllalu);
			$nopo = $res[0]['nopo'];
			$tanggalpo = $res[0]['tanggalpo'];
			$hargapo = $res[0]['hargapo'];
			
			if($selisitgl < 0){
				// throw new PDOException("Tanggal perubahan tidak bisa lebih kecil dari tanggal pembelian terakhir (".tanggalnormal($tgllalu).").");
			}
			
			$str="update ".$dbname.".log_5hargaterakhir set status='0' where pt='".$pt."' and unit='".$unit."' and kodebarang='".$kodebrg."'";
			$owlPDO->exec($str);
			
			$str="insert into ".$dbname.".log_5hargaterakhir  (pt,unit,kodebarang,tanggal,hargasatuan,hargaestimasi,status,nopo,createdby,createtime,updateby,updatetime) values ('".$pt."','".$unit."','".$kodebrg."','".$tgl."','".$harga."','".$hargaestimasi."','1','".$nopo."','".$_SESSION['standard']['userid']."','".$time."','".$_SESSION['standard']['userid']."','".$time."')";
			
			$str = "insert into ".$dbname.".log_5hargaterakhir (pt,unit,kodebarang,tanggal,hargasatuan,hargaestimasi,status,nopo,createdby,createtime,updateby,updatetime) values ('".$pt."','".$unit."','".$kodebrg."','".$tgl."','".$harga."','".$hargaestimasi."','1','".$nopo."','".$_SESSION['standard']['userid']."','".$time."','".$_SESSION['standard']['userid']."','".$time."')";
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning \n" . addslashes($e->getMessage());
		}            
	break;


    case'loadData':
		$tab="";
		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>" . $_SESSION['lang']['pt'] . "</td>
				<td align=center>" . $_SESSION['lang']['unit'] . "</td>
				<td align=center>Kode Barang</td>
				<td align=center>Nama Barang</td>
				<td align=center>Tanggal</td>
				<td align=center>Harga Satuan</td>
				<td align=center>No. PO</td>
				<td align=center>Harga Estimasi</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
				<td align=center>" . $_SESSION['lang']['action'] . "</td>
			</tr>
			</thead>
			<tbody>";
		
		$whr="";
		if ($ptscr!='') {
            $whr=" and pt ='".$ptscr."'";
        }
        if (@$unitsrc!='') {
            $whr.=" and unit='".$unitsrc."'";
        }
        if (@$kodebrgsrc!='') {
            $whr.=" and kodebarang like '%".$kodebrgsrc."%'";
        }
        if (@$tglsrc!='') {
            $whr.=" and tanggal='".$tglsrc."'";
        }

        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        @$offset = $page * $limit;
        @$maxdisplay = ($page * $limit);
		
		$str="select * from ".$dbname.".log_5hargaterakhir where status='1' ".$whr."";
		$res=fetchdata($str);
		$jlhbrs=count($res);
		if($jlhbrs <= '0'){
			$tab.="<tr class=rowcontent><td colspan=12 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no=0;
			$str="select * from ".$dbname.".log_5hargaterakhir where status='1' ".$whr." order by pt asc, unit asc, kodebarang asc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['pt']."'");
				$optunit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['unit']."'");
				$optNmbrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
				$optnmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
				
				$tab.="<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td align=left>".($val['pt']==''?'Global':$optpt[$val['pt']])."</td>
					<td align=left>".($val['pt']==''?'Global':$optunit[$val['unit']])."</td>
					<td align=right>".$val['kodebarang']."</td>
					<td align=left>".$optNmbrg[$val['kodebarang']]."</td>
					<td align=center style='min-width:80px'>".tanggalnormal($val['tanggal'])."</td>
					<td align=right>".hidezerodecimal($val['hargasatuan'],2)."</td>
					<td align=left>".$val['nopo']."</td>
					<td align=right>".hidezerodecimal($val['hargaestimasi'],2)."</td>
					<td align=left>".(isset($optnmkar[$val['updateby']])?$optnmkar[$val['updateby']]:'')."</td>
					<td align=center>
						<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$val['pt']."','".$val['unit']."','".$val['kodebarang']."','".tanggalnormal($val['tanggal'])."','".hidezerodecimal($val['hargasatuan'],2)."','".$optNmbrg[$val['kodebarang']]."','".hidezerodecimal($val['hargaestimasi'])."','".$val['nopo']."');\">
						<img src=images/skyblue/zoom.png class=resicon  caption='Detail' onclick=\"showdetail('".$val['pt']."','".$val['unit']."','".$val['kodebarang']."',event);\">
					</td>
				</tr>";
			}
			
			## PAGING
			$colspan=11;
			$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loadData','getPage');
			$tab.="</table>";
		}
		
		echo $tab;
	break;
	
	case'showdetail':
		$tab="";
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=3 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['pt']."</td>
				<td align=center>".$_SESSION['lang']['unit']."</td>
				<td align=center>Kode Barang</td>
				<td align=center>Nama Barang</td>
				<td align=center>Tanggal</td>
				<td align=center>No. PO</td>
				<td align=center>No. PO</td>
				<td align=center>Harga Estimasi</td>
				<td align=center>" . $_SESSION['lang']['updateby'] . "</td>
			</tr>
			</thead>
			<tbody>";
			
		$str="select * from ".$dbname.".log_5hargaterakhir where pt='".$pt."' and unit='".$unit."' and kodebarang='".$kodebrg."' order by tanggal desc, status desc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
				
			$optpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['pt']."'");
			$optunit = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['unit']."'");
			$optNmbrg=makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			$optnmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
			
			$tab.="<tr class=rowcontent>
				<td align=center>".$no."</td>
				<td align=left>".($val['pt']==''?'Global':$optpt[$val['pt']])."</td>
				<td align=left>".($val['pt']==''?'Global':$optunit[$val['unit']])."</td>
				<td align=right>".$val['kodebarang']."</td>
				<td align=left>".$optNmbrg[$val['kodebarang']]."</td>
				<td align=center style='min-width:80px'>".tanggalnormal($val['tanggal'])."</td>
				<td align=right>".hidezerodecimal($val['hargasatuan'],2)."</td>
				<td align=left>".$val['nopo']."</td>
				<td align=right>".hidezerodecimal($val['hargaestimasi'],2)."</td>
				<td align=left>".(isset($optnmkar[$val['updateby']])?$optnmkar[$val['updateby']]:'')."</td>
			</tr>";
		}
		$tab.="</tbody>
		</table>";
	
		echo $tab;
	break;
	
	case'cariBarangDlmDtBs':
    
        $str="select * from ".$dbname.".log_5masterbarang where (namabarang like '%".$txtfind."%' or kodebarang like '%".$txtfind."%')";
        if($res=$owlPDO->query($str))
        {
            echo "<fieldset>
                <legend>Result</legend>
                <div style=\"overflow:auto; max-height:300px;\" >
                <table class=sortable cellspacing=1 cellpadding=2  border=0>
                    <thead>
                    <tr class=rowheader>
                        <td class=firsttd align=center>No.</td>
                        <td align=center>".$_SESSION['lang']['kelompokbarang']."</td>
                        <td align=center>".$_SESSION['lang']['subkelompokbarang']."</td>
                        <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                        <td>".$_SESSION['lang']['namabarang']."</td>
                        <td align=center>".$_SESSION['lang']['satuan']."</td>
                    
                       
                    </tr>
                    </thead>
                    <tbody>";
                    
            $no=0;   
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $no+=1;
                echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg('".$bar->kodebarang."','".changeKutipChar($bar->namabarang)."','".$bar->satuan."')\" title='Click' >";
              
                $optNm=makeOption($dbname, 'log_5klbarang', 'kode,kelompok',"kode='".substr($bar->kodebarang,0,3)."'");
                $optsklbrg=makeOption($dbname,'log_5subklbarang','kode,namasubkelompok',"kode='".substr($bar->kodebarang,0,5)."'");
                
                echo "<td class=firsttd  align=center>".$no."</td>
                      <td>".$optNm[substr($bar->kodebarang,0,3)]."</td>
                      <td>".$optsklbrg[substr($bar->kodebarang,0,5)]."</td>
                      <td align=center>".$bar->kodebarang."</td>
                      <td>".$bar->namabarang."</td>
                      <td align=center>".$bar->satuan."</td>
        
            </tr>";
            }    
               
        }else{
            echo " Gagal,".PDOException::getMessage();
        }
    break;

    default:
	break;
}
?>
