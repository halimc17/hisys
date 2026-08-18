<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$notransaksi=checkPostGet('notransaksi','');
$tgl=checkPostGet('tgl','');
$unit=checkPostGet('unit','');
$bytgl1=tanggalsystemn(checkPostGet('bytgl1',''));
$bytgl2=tanggalsystemn(checkPostGet('bytgl2',''));
$frek=checkPostGet('frek','');
$bykel=checkPostGet('bykel','');
$bydet=checkPostGet('bydet','');
$byrp=checkPostGet('byrp','');
$byket=checkPostGet('byket','');
$option_penginapan=checkPostGet('option_penginapan','');
$fileupload = checkPostGet('fileupload', '');
$id = checkPostGet('id', '');
$file = checkPostGet('file', '');
$doc = checkPostGet('doc', '');
$path='fileupload/pjdinas/';
$emodul="PJD";

//post array
$kodekelompok = checkPostGet('kodekelompok','');
$rupiah = checkPostGet('rupiah','');

$nmtipe=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');

switch($method){   

    case'posting':
		$str1="select notransaksi from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=1 ";
		$data = fetchdata($str1);
		if(count($data) > 0){
			$strht = "update " . $dbname . ".sdm_pjdinasht set posting='1', tglposting='".date('Y-m-d')."' where notransaksi='" . $notransaksi . "'";
			try {
				$owlPDO->exec($strht);
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>"; 
				die(); 
			}
		}else{
			echo "Anda tidak dapat posting, Silahkan isi PJD terlebih dahulu.";
		}
    break;


	case 'inserttgjwb':
		
		/*	
		$str1="select jenisbiaya,jumlahhrd from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=0 ";
		$data = fetchdata($str1);
		$plafon = array();
		for($i=0; $i<count($data); $i++){
			$plafon["jenis_".$data[$i]['jenisbiaya']] = $data[$i]['jumlahhrd'];
		}
		$str2="select jenisbiaya,sum(jumlah) as jumlah from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber=1 group by jenisbiaya ";
		$tetulis = array();
		for($i=0; $i<count($data); $i++){
			$tetulis["jenis_".$data[$i]['jenisbiaya']] = $data[$i]['jumlah'];
		}
		$tervaludasi = array();
		for($i=0; $i<count($kodekelompok); $i++){
			$Cektertulis = ((double)@$tetulis["jenis_".$kodekelompok[$i]]+(double)$rupiah[$i]);
			if($Cektertulis != "" and (double)$Cektertulis > (double)$plafon["jenis_".$kodekelompok[$i]]){
				$tervaludasi['rupiah_'.$kodekelompok[$i]] = ((double)$plafon["jenis_".$kodekelompok[$i]]-(double)$Cektertulis);
			}
		}
		
		if(count($tervaludasi) == 0){*/
		$result['status'] = "false";
		$result['message'] = "Tidak ada eksekusi data!";
		if(count($kodekelompok) > 0){
			$strDeleteFirst="delete from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggal='".$bytgl1."' and sumber='1'";
			$str="";
			for($i=0; $i<count($kodekelompok); $i++){
				if($kodekelompok[$i] != 3){
					$penginapan = 0;
				}else{
					$penginapan = $option_penginapan;
				}
				if($i == 0){
					$str.="INSERT INTO ".$dbname.".`sdm_pjdinasdt` 
						(`notransaksi`, `jenisbiaya`, `detail`,`keterangan`,`tanggal`,`tanggalsampai`,`jumlah`,`jumlahhrd`,`updateby`,`sumber`,`flag`)
				values ('".$notransaksi."','".$kodekelompok[$i]."','".$bydet."','".$byket."','".$bytgl1."','".$bytgl2."','".$rupiah[$i]."','".$rupiah[$i]."','".$_SESSION['standard']['userid']."','1','".$penginapan."')";
				}else{
					$str .= ",('".$notransaksi."','".$kodekelompok[$i]."','".$bydet."','".$byket."','".$bytgl1."','".$bytgl2."','".$rupiah[$i]."','".$rupiah[$i]."','".$_SESSION['standard']['userid']."','1','".$penginapan."')";
				}
				
			}
			$str .= ";";
			try{
				$owlPDO->exec($strDeleteFirst);//delete First
				$owlPDO->exec($str);
				$result['status'] = "true";
				$result['message'] = "Berhasil";
			}catch (PDOException $e) {
				$result['status'] = "false";
				$result['message'] = "Simpan tidak berhasil, ".$e->getMessage();
			}
		}else{
			$result['status'] = "false";
			$result['message'] = "Tidak ada eksekusi data detail.";
		}
		/*}else{
			$result['status'] = "false";
			$result['message'] = $tervaludasi;
		}*/
		echo json_encode($result);
    break;
		
    
    case 'insert':
    if ($unit=='') {
    	exit('Warning : Unit Kosong');
    }
		$arrbgt=validasibudget('PJD',$unit);
		$tahun=substr($tgl,6,4);
		if ($arrbgt['status']=='1') {
			$realisasipjd=realisasipjd($unit,$tahun);
			$realisasibgt=bgtpjd($unit,$tahun);

			if ($realisasibgt<$realisasipjd) {
				exit('Warning : Nilai Butget Lebih kecil Dari Realisasi PJD !!');
			}
			else
			{
				$str="INSERT INTO ".$dbname.".`sdm_pjdinasdt` 
				(`notransaksi`, `jenisbiaya`, `frekuensi`, `detail`,`keterangan`,`tanggal`,`tanggalsampai`,`jumlah`,`jumlahhrd`,`updateby`)
		        values ('".$notransaksi."','".$bykel."','".$frek."','".$bydet."','".$byket."','".$bytgl1."','".$bytgl2."','".$byrp."','".$byrp."','".$_SESSION['standard']['userid']."')";
		        try{
		            $owlPDO->exec($str);
		        }
		        catch (PDOException $e) {
		            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
		            die(); 
		        }
			}
		}else{
				$str="INSERT INTO ".$dbname.".`sdm_pjdinasdt` 
						(`notransaksi`, `jenisbiaya`, `frekuensi`, `detail`,`keterangan`,`tanggal`,`tanggalsampai`,`jumlah`,`jumlahhrd`,`updateby`)
		        values ('".$notransaksi."','".$bykel."','".$frek."','".$bydet."','".$byket."','".$bytgl1."','".$bytgl2."','".$byrp."','".$byrp."','".$_SESSION['standard']['userid']."')";
		        try{
		            $owlPDO->exec($str);
		        }
		        catch (PDOException $e) {
		            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
		            die(); 
		        }
		}

		#update di ht
		$jumlah = 0;
		$str="select sum(jumlahhrd) as jumlah from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber='0' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$jumlah=$bar['jumlah'];
			
		#update ke ht	
		$str="update ".$dbname.".sdm_pjdinasht set uangmuka='".$jumlah."' where notransaksi='".$notransaksi."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }	
		
		
    break;

   
    
    case 'delete':
        $str="delete from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and jenisbiaya='".$bykel."' and detail='".$bydet."' and sumber='0' ";

        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
		
		#update di ht
		$str="select sum(jumlah) as jumlah from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlah=$bar['jumlah'];
			
		#update ke ht	
		$str="update ".$dbname.".sdm_pjdinasht set uangmuka='".$jumlah."' where notransaksi='".$notransaksi."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }	
		
    break;
    case'loadnamakelompok':		
			
		
		//exit("error".$optkel);
	break;
    case'loaddata':
            $html = "<div id=container>
                    <table class=sortable cellspacing=1 border=0>
					 <thead>
						 <tr class=rowheader>
							<td align=center>No</td>
							<td align=center>".$_SESSION['lang']['namakelompok']."</td>
							<td align=center>".$_SESSION['lang']['rupiah']."</td> 
							<td align=center>".$_SESSION['lang']['keterangan']."</td> 
							<td align=center>".$_SESSION['lang']['action']."</td></tr>
						 </tr>
                        </thead>
                        <tbody>";
			
            $str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber = '0' order by jenisbiaya ";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
			$harga = 0;
            while($bar=$res->fetch()){
                $no+=1;
				$harga = $bar['frekuensi']*$bar['jumlah'];
                $html .= "<tr class=rowcontent>";
                $html .= "<td align=center>".$no."</td>";
				$html .= "<td align=left>".$nmtipe[$bar['jenisbiaya']]."</td>";
				$html .= "<td align=right>".number_format($bar['jumlah'],2)."</td>";
				$html .= "<td align=left>".$bar['keterangan']."</td>";
                $html .= "<td align=center>
						<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"bydel('".$bar['notransaksi']."','".$bar['jenisbiaya']."','".$bar['detail']."');\">
                        </td>";
                $html .= "</tr>";
				@$tby+=$bar['jumlah'];
            }
			 $html .= "<tr class=rowcontent>";
                $html .= "<td align=center colspan=2>".$_SESSION['lang']['total']."</td>";
				$html .= "<td align=right>".number_format(@$tby,2)."</td>";
				$html .= "<td align=left colspan=2></td>";
                $html .= "</tr>";
			
			
			$html .="</table>";
           
		   
		   $str="SELECT sdm_5jenisbiayapjdinas.*
				FROM ".$dbname.".sdm_5jenisbiayapjdinas 
					LEFT JOIN ".$dbname.".sdm_pjdinasdt 
					ON sdm_5jenisbiayapjdinas.id = sdm_pjdinasdt.jenisbiaya 
					AND sdm_pjdinasdt.notransaksi='".$notransaksi."' 
					AND sdm_pjdinasdt.sumber='0'
				WHERE  sdm_pjdinasdt.jenisbiaya IS NULL 
				GROUP  BY sdm_5jenisbiayapjdinas.id 
				ORDER  BY sdm_5jenisbiayapjdinas.id";

			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$optkel ="";
			while($bar=$res->fetch()){
				$optkel.="<option id=jenisbiaya".$bar['id']." value=".$bar['id'].">".$bar['keterangan']."</option>";
			}
			$data['datatable'] = $html;
			$data['option']	   =$optkel;
			
			echo json_encode($data);
			
    break;

	case 'uploaddata':
		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
		
		$str="select posting from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$posting = $res[0]['posting'];
		
		$table .="<fieldset style='float:left;'>
		<legend>Upload File</legend>
		<table class=sortable cellspacing=1 cellpadding=5 border=0>
			<thead> 
			<tr>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kriteria']."</td>
				<td align=center>".$_SESSION['lang']['namafile']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody id=containerupload></tbody>";
			
			if($posting=='0'){
				$table.="<tbody>
				<tr>
					<td></td>
					<td>
						<select id='kriteriaefil'>". $optkriteria."</select>
					</td>
					<td>
						<input type='file' name='upload' id='upload' class=mybutton>
					</td>
					<td style='text-align:center'>
						<img src=images/plus.png class=resicon id='addfile'  title='Add File ' onclick=\"addfile('".$notransaksi."');\">
					</td>
				</tr>
				</tbody>";
			}
		$table.="</table>
		</fieldset>";
		
		echo $table;
	
        
        // echo "
        // <fieldset style=float:left>
            // <legend>".$_SESSION['lang']['uploaddata']."</legend>
            // <table>
                // <tr>
                    // <td><input name=fileupload type=file id=fileupload size=1 class=mybutton style=width:160px>
                    // </td>
                    // <td>
                        // <button class=mybutton onclick=simpanupload('".$notransaksi."')>".$_SESSION['lang']['save']."</button>
                    // </td>
                // </tr>
            // </table>
        // </fieldset><br><br><br><br>";

        // echo"<fieldset style=float:left>
            // <legend>".$_SESSION['lang']['list']."</legend>
        // <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        // <thead>
        // <tr class=rowheader>    
            // <td align=center>".$_SESSION['lang']['nourut']."</td>
            // <td align=center>".$_SESSION['lang']['namafile']."</td>
            // <td align=center>".$_SESSION['lang']['action']."</td>
        // </tr>
        // </thead>";

        // $no = 0;
        // $str="select * from ".$dbname.".file_pjdinas where notransaksi='".$notransaksi."'";
        // $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        // $res->setFetchMode(PDO::FETCH_OBJ);
        // while ($bar = $res->fetch()) {
            // $no+=1;
            // echo"<tr class=rowcontent>   
                // <td align=center>".$no."</td>
                // <td ><a style=cursor:pointer; onclick=\"displayfile('".$bar->namafile."','event');\" href='#'>".$bar->namafile."</a></td>
                // <td align=center>
                    // <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delfile('".$notransaksi."','".$bar->id."');\" >
                // </td>
                // </tr>";
        // }
        // echo "</table></fieldset>";

    break;

    case 'simpanupload':
		$data=$_POST;
		$tgl = date("YmdHis");
        $his = date("His");
        $data = $_POST;
        if($data['fileupload']!=''){
            if($_FILES['file']['error']==0){    
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
                $filename = $newfilename."_".$tgl."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    if($_FILES['file']['size'] <= 512000){
                        $str = "insert into ".$dbname.".file_pjdinas values ('','".$notransaksi."','".$filename."','".$filetype."','".$data['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
                        try{
                            $owlPDO->exec($str);
                            if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                            file_put_contents($path.$filename,$file_tmpname);
                        }
                        catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }else{
                        exit("warning : Ukuran file upload maksimal 512kb");
                    }
                }else{
                    exit("Warning : Format file upload harus .jpg | .jpeg | .png | .pdf | .xls | .xlsx | .doc | .docx");
                }
            }
        }
    break;
	
	case'loadfiles':
		$data=$_POST;
		$tab="";
		$no=0;
		$str="select * from ".$dbname.".file_pjdinas where notransaksi='".$notransaksi."'";
        $res=fetchData($str);
		if(count($res) <= 0){
			$tab.="<tr class='rowcontent'><td colspan='4' style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td></tr>";
		}else{
			foreach($res as $val){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td style='text-align:center'>".getcriterianame($val['kriteriaefil'])."</td>";
				$tab.="<td style='text-align:left;'>".$val['namafile']."</td>";
				
				$tab.="<td align=center>
						<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>";
				
				$strx="select posting from ".$dbname.".sdm_pjdinasht where notransaksi='".$notransaksi."'";
				$resx = fetchData($strx);
				if($resx[0]['posting'] == '0'){
					$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$notransaksi."','".$val['namafile']."');\" >";
				}
				$tab."  </td>
				</tr>";
			}
		}
		
		echo $tab;
	break;
	
	case 'deletefile':
		$data=$_POST;
        $str="delete from ".$dbname.".file_pjdinas where notransaksi='".$notransaksi."' and namafile='".$data['namafile']."'";
        try{
            $owlPDO->exec($str);
            $pathx = $path.$data['namafile'];
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    case'delfile':
        $str="delete from ".$dbname.".`file_pjdinas` where notransaksi='".$notransaksi."' and id='".$id."'";
            try{$owlPDO->exec($str); }
            catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
        }   
    break;

    case'displayfile':
    
    $potong=explode('.',$doc);
    if($potong[1]=='pdf')
    {
        echo"<embed src='fileupload/pjdinas/".$doc."' width=780px height=370px>";
    }
    else
    {
        echo"<img src='fileupload/pjdinas/".$doc."'>";
    }


default:
}

function realisasipjd($unit,$tahun)
{
	global $dbname;
	global $owlPDO;
	
	$value = 0;
	$str="select sum(a.jumlah) as jumlah from ".$dbname.".sdm_pjdinasdt a left join sdm_pjdinasht b on a.notransaksi=b.notransaksi where b.unit='".$unit."' and LEFT(b.tanggalperjalanan,4)='".$tahun."'";
	$res=fetchdata($str);
	if(!empty($res)){
		$value = $res[0]['jumlah'];
	}
	
	return $value;
}

function bgtpjd($unit,$tahun)
{
	global $dbname;
	global $owlPDO;
	
	$value = 0;
	if ($unit!='') {
		$str="select sum(rupiah) as jumlah from ".$dbname.".bgt_budget where left(kodeorg,4)='".$unit."' and LEFT(tahunbudget,4)='".$tahun."' and noakun='7130102'";
	}
	else
	{
		$str="select sum(rupiah) as jumlah from ".$dbname.".bgt_budget where left(kodeorg,4)='".$unit."' and LEFT(tahunbudget,4)='".$tahun."' and noakun='8210403'";
	}
	
	$res=fetchdata($str);
	if(!empty($res)){
		$value = $res[0]['jumlah'];
	}
	
	return $value;
}

?>