<?php
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

$tab=$param['tab'];

switch($tab){
	case'fileSelected':
	
		$tab="";
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
								if($param['kodebudget']!='' and $param['kodebudget']!='undefined'){
									$kodebudget = $param['kodebudget'];
								}else{									
									$kodebudget = "M-".substr($sheet['A'],0,3);
								}
								
								$str = "select * from ".$dbname.".bgt_budget where tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."' and tipebudget='".$param['tipebgt']."' and kodebudget='".$kodebudget."' and kodebarang='".$sheet['A']."'";
								$res = fetchdata($str);
								if(count($res)>0){
									$data = array(
										'tahunbudget'=> $param['tahun'],
										'kodeorg'    => $param['kodeorg'],
										'tipebudget' => $param['tipebgt'],
										'kodebudget' => $kodebudget,
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
									$str="insert into ".$dbname.".bgt_budget (tahunbudget, kodeorg, tipebudget, kodebudget,rupiah, kodebarang, regional, updateby,jumlah,satuanj) 
									values('".$param['tahun']."','".$param['kodeorg']."','".$param['tipebgt']."','".$kodebudget."','".$sheet['B']*$harga[$sheet['A']]."','".$sheet['A']."','".$region."','".$_SESSION['standard']['userid']."','".$sheet['B']."','".$nmsat[$sheet['A']]."')";
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
							$tab.="<tr class=rowcontent>";
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
						}
					}
				}
			}else{
				exit("Warning : Format file upload harus .xlsx");
			}
		}
		
		echo $tab;
	break;
	case'showupload':
		$tab="";
		$tab.="<fieldset><legend>Upload / Download</legend>
		<table border=0>
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
					<input hidden id=kodebudgetupload value=".$param['kodebudget'].">
					<button id=btnsubmit class=mybutton onclick=\"fileSelected()\">Preview</button>
				</td>
			</tr>
		</table>
		</fieldset>";
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<div style='overflow:auto;max-height:400px'>
			<table class='sortable' cellspacing='1' border='0'>
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
	
	case'cekclose':
		$tipebudget =$_POST['tipebudget'];
		$tahunbudget=$_POST['tahunbudget'];
		$kodews     =$_POST['kodews'];

		$str="select * from ".$dbname.".bgt_budget
			where tutup = 1 and tipebudget = '".$tipebudget."' and tahunbudget ='".$tahunbudget."' and kodeorg ='".$kodews."'
			limit 0, 1    
			";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$hkef='';
		while($bar= $res->fetch()){
			$hkef.="Budget has been closed.";
		}
		if($hkef!='')echo $hkef;    
	break;
	case'0':
		$tipebudget     =$_POST['tipebudget'];
		$tahunbudget    =$_POST['tahunbudget'];
		$kodews         =$_POST['kodews'];
		$kodebudget0    =$_POST['kodebudget0'];
		$hkefektif0     =$_POST['hkefektif0'];
		$jumlahpersonel0=$_POST['jumlahpersonel0'];
		$totalbiaya0    =$_POST['totalbiaya0'];
		
		if($param['proses']=='update'){
			$volume0=$hkefektif0*$jumlahpersonel0;

			$str="update ".$dbname.".`bgt_budget` set
				`tipebudget` = '".$tipebudget."',
				`tahunbudget`= '".$tahunbudget."',
				`kodeorg`    = '".$kodews."',
				`kodebudget` = '".$kodebudget0."',
				`volume`     = '".$volume0."',
				`satuanv`    = 'hk',
				`jumlah`     = '".$jumlahpersonel0."',
				`satuanj`    = 'orang',
				`rupiah`     = '".$totalbiaya0."',
				`updateby`   = '".$_SESSION['standard']['userid']."',
				`lastupdate` = CURRENT_TIMESTAMP 
				where kunci  = '".$param['index']."'
			";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
		}else{
			$str="select * from ".$dbname.".bgt_budget where tipebudget = '".$tipebudget."' and tahunbudget ='".$tahunbudget."' and kodeorg ='".$kodews."' and kodebudget ='".$kodebudget0."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Warning : Data sudah ada ".$res[0]['kodebudget']." ".$res[0]['jumlah']." orang.");
			}
			
			$volume0=$hkefektif0*$jumlahpersonel0;
			$str="INSERT INTO ".$dbname.".`bgt_budget` (
				`tipebudget` ,
				`tahunbudget` ,
				`kodeorg` ,
				`kodebudget` ,
				`volume` ,
				`satuanv` ,
				`jumlah` ,
				`satuanj` ,
				`rupiah` ,
				`updateby` ,
				`lastupdate` 
			)VALUES (
				'".$tipebudget."', '".$tahunbudget."', '".$kodews."', '".$kodebudget0."', '".$volume0."', 'hk' , '".$jumlahpersonel0."', 'orang' , '".$totalbiaya0."', '".$_SESSION['standard']['userid']."',CURRENT_TIMESTAMP 
			)";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
		}
		
	break;
	case'1':
		$tipebudget =$_POST['tipebudget'];
		$tahunbudget=$_POST['tahunbudget'];
		$kodews     =$_POST['kodews'];
		$kodebudget1=$_POST['kodebudget1'];
		$totalharga1=$_POST['totalharga1'];
		$kodebarang1=$_POST['kodebarang1'];
		$regional1  =$_POST['regional1'];
		$jumlah1    =$_POST['jumlah1'];
		$satuan1    =$_POST['satuan1'];
		
		if($param['proses']=='update'){
			$str="update ".$dbname.".`bgt_budget` set
				`tipebudget` = '".$tipebudget."',
				`tahunbudget`= '".$tahunbudget."',
				`kodeorg`    = '".$kodews."',
				`kodebudget` = '".$kodebudget1."',
				`regional`   = '".$regional1."',
				`kodebarang` = '".$kodebarang1."',
				`jumlah`     = '".$jumlah1."',
				`satuanj`    = '".$satuan1."',
				`rupiah`     = '".$totalharga1."',
				`updateby`   = '".$_SESSION['standard']['userid']."',
				`lastupdate` = CURRENT_TIMESTAMP
				where kunci  ='".$param['index']."'
			";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
		}else{
			$str="select * from ".$dbname.".bgt_budget where tipebudget = '".$tipebudget."' and kodebudget like 'M%' and tahunbudget ='".$tahunbudget."' and kodeorg ='".$kodews."' and kodebarang ='".$kodebarang1."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Warning : Data sudah ada ".$res[0]['kodebarang']." ".$res[0]['jumlah']." ".$res[0]['satuanj'].".");
			}
			
			$str="INSERT INTO ".$dbname.".`bgt_budget` (
				`tipebudget` ,
				`tahunbudget` ,
				`kodeorg` ,
				`kodebudget` ,
				`regional` ,
				`kodebarang` ,
				`jumlah` ,
				`satuanj` ,
				`rupiah` ,
				`updateby` ,
				`lastupdate` 
			)VALUES (
				'".$tipebudget."', '".$tahunbudget."', '".$kodews."', '".$kodebudget1."', '".$regional1."', '".$kodebarang1."', '".$jumlah1."', '".$satuan1."', '".$totalharga1."', '".$_SESSION['standard']['userid']."',CURRENT_TIMESTAMP 
			)";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	case'2':
		$tipebudget =$_POST['tipebudget'];
		$tahunbudget=$_POST['tahunbudget'];
		$kodews     =$_POST['kodews'];
		$kodebudget2=$_POST['kodebudget2'];
		$totalharga2=$_POST['totalharga2'];
		$kodebarang2=$_POST['kodebarang2'];
		$regional2  =$_POST['regional2'];
		$jumlah2    =$_POST['jumlah2'];
		$satuan2    =$_POST['satuan2'];
		
		if($param['proses']=='update'){
			$str="update ".$dbname.".`bgt_budget` set
				`tipebudget` = '".$tipebudget."',
				`tahunbudget`= '".$tahunbudget."',
				`kodeorg`    = '".$kodews."',
				`kodebudget` = '".$kodebudget2."',
				`regional`   = '".$regional2."', 
				`kodebarang` = '".$kodebarang2."', 
				`jumlah`     = '".$jumlah2."',
				`satuanj`    = '".$satuan2."',
				`rupiah`     = '".$totalharga2."', 
				`updateby`   = '".$_SESSION['standard']['userid']."',
				`lastupdate` = CURRENT_TIMESTAMP
				where kunci  = '".$param['index']."'
			";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
		}else{
			$str="select * from ".$dbname.".bgt_budget where tipebudget = '".$tipebudget."' and kodebudget like 'TOOL%' and tahunbudget ='".$tahunbudget."' and kodeorg ='".$kodews."' and kodebarang ='".$kodebarang2."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Warning : Data sudah ada ".$res[0]['kodebarang']." ".$res[0]['jumlah']." ".$res[0]['satuanj'].".");
			}
			$str="INSERT INTO ".$dbname.".`bgt_budget` (
				`tipebudget` ,
				`tahunbudget` ,
				`kodeorg` ,
				`kodebudget` ,
				`regional` ,
				`kodebarang` ,
				`jumlah` ,
				`satuanj` ,
				`rupiah` ,
				`updateby` ,
				`lastupdate` 
			)VALUES (
				'".$tipebudget."', '".$tahunbudget."', '".$kodews."', '".$kodebudget2."', '".$regional2."', '".$kodebarang2."', '".$jumlah2."', '".$satuan2."', '".$totalharga2."', '".$_SESSION['standard']['userid']."',CURRENT_TIMESTAMP 
			)";
		   try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	case'3':
		$tipebudget =$_POST['tipebudget'];
		$tahunbudget=$_POST['tahunbudget'];
		$kodews     =$_POST['kodews'];
		$kodebudget3=$_POST['kodebudget3'];
		$totalbiaya3=$_POST['totalbiaya3'];
		$kodeakun3  =$_POST['kodeakun3'];
		
		if($param['proses']=='update'){
			$str="update ".$dbname.".`bgt_budget` set
				`tipebudget` = '".$tipebudget."', 
				`tahunbudget`= '".$tahunbudget."', 
				`kodeorg`    = '".$kodews."', 
				`kodebudget` = '".$kodebudget3."', 
				`noakun`     = '".$kodeakun3."', 
				`rupiah`     = '".$totalbiaya3."', 
				`updateby`   = '".$_SESSION['standard']['userid']."',
				`lastupdate` = CURRENT_TIMESTAMP
				where kunci  = '".$param['index']."'
			";
			try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
		}else{
			$str="select * from ".$dbname.".bgt_budget where tipebudget = '".$tipebudget."' and kodebudget like 'TRANSIT%' and tahunbudget ='".$tahunbudget."' and kodeorg ='".$kodews."' and noakun ='".$kodeakun3."'";
			$res = fetchdata($str);
			if(count($res)>0){
				exit("Warning : Data sudah ada ".$res[0]['noakun']." ".$res[0]['rupiah'].".");
			}
			$str="INSERT INTO ".$dbname.".`bgt_budget` (
				`tipebudget` ,
				`tahunbudget` ,
				`kodeorg` ,
				`kodebudget` ,
				`noakun` ,
				`rupiah` ,
				`updateby` ,
				`lastupdate` 
			)VALUES (
				'".$tipebudget."', '".$tahunbudget."', '".$kodews."', '".$kodebudget3."', '".$kodeakun3."', '".$totalbiaya3."', '".$_SESSION['standard']['userid']."',CURRENT_TIMESTAMP 
			)";
		   try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	case'4':
		$tipebudget =$_POST['tipebudget'];
		$tahunbudget=$_POST['tahunbudget'];
		$kodews     =$_POST['kodews'];
		$kunci      =$_POST['kunci'];

		$str="update ".$dbname.".bgt_budget set tutup='1' where kunci ='".$kunci."'";
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		
	break;
}
?>