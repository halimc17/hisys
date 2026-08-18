<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$method = checkPostGet('method', '');
$pid = checkPostGet('pid', '');
$nama = checkPostGet('nama', '');
$alamat = checkPostGet('alamat', '');
$desa = checkPostGet('desa', '');
$kecamatan = checkPostGet('kecamatan', '');
$kabupaten = checkPostGet('kabupaten', '');
$kodebank = checkPostGet('kodebank', '');
$namapemilikrek = checkPostGet('namapemilikrek', '');
$norek = checkPostGet('norek', '');
$ktp = checkPostGet('ktp', '');
$hp = checkPostGet('hp', '');
$unitbawah = checkPostGet('unitbawah', '');
$pt = checkPostGet('pt', '');
$jenisupload = checkPostGet('jenisupload', '');
$xxx = checkPostGet('xxx', '');
$yyy = checkPostGet('yyy', '');
$namafile = checkPostGet('namafile', '');
$path = "fileupload/lgl_GRLTT/";
$idcari = checkPostGet('idcari', '');
$namacari = checkPostGet('namacari', '');
$alamatcari = checkPostGet('alamatcari', '');
$ktpcari = checkPostGet('ktpcari', '');

$kriteria = checkPostGet('kriteria', '');
$kriteriax = checkPostGet('kriteriax', '');
$id = checkPostGet('id', '');
$emodul = 'DNM';

switch ($method) {
    case 'getkecamatan':
    $str = "select b.idkec, b.kecamatan from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".kecamatan b on a.kecamatan=b.idkec 
        where a.kabupaten='".$kabupaten."' group by a.kecamatan order by b.kecamatan";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $optkecamatan.="<option value='" . $bar->idkec . "'>" . $bar->kecamatan . "</option>";
    }

    echo $optkecamatan;
    break;
    case 'getdesa':
    $str = "select b.iddes, b.desa from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".desa b on a.desa=b.iddes and a.kecamatan=b.id_kec 
        where a.kecamatan='".$kecamatan."' group by a.desa order by b.desa";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        $optdesa.="<option value='" . $bar->iddes . "'>" . $bar->desa . "</option>";
    }

    echo $optdesa;
    break;
    case 'getdesa2':
    $str = "select b.iddes, b.desa from " . $dbname . ".pad_5desa a 
        left join ".$dbname.".desa b on a.desa=b.iddes and a.kecamatan=b.id_kec 
        where a.kecamatan='".$kecamatan."' group by a.desa order by b.desa";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        if($desa==$bar->iddes)
        {
          $optdesa.="<option value='" . $bar->iddes . "' selected>" . $bar->desa . "</option>";
        }
        else
        {
          $optdesa.="<option value='" . $bar->iddes . "'>" . $bar->desa . "</option>";
        }
        
    }

    echo $optdesa;
    break;
    case 'excel':

        $str1 = "select a.*,b.unit, c.namabank from " . $dbname . ".pad_5masyarakat a
            left join " . $dbname . ".pad_5desa b on a.desa=b.namadesa 
            left join ". $dbname.".keu_5daftarbank c on a.kodebank=c.kodebank
            where b.unit like '" . $unitbawah . "%' order by a.desa,a.nama";
        $stream.="<table class=sortable cellspacing=1 border=1>
     <thead>
             <tr bgcolor='#dedede'>
                <td style='width:150px;'>" . $_SESSION['lang']['kodeorg'] . "</td>              
                <td style='width:150px;'>" . $_SESSION['lang']['nama'] . "</td>                    
                <td style='width:150px;'>" . $_SESSION['lang']['alamat'] . "</td>                        
                <td style='width:150px;'>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['desa'] . "</td>
                <td>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['kecamatan'] . "</td>                  
                <td>" . $_SESSION['lang']['kabupaten'] . "</td>                   
                <td>" . $_SESSION['lang']['namabank'] . "</td>    
                <td style='width:150px;'>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['pemilik'] . " " . $_SESSION['lang']['rekening'] . "</td>
                <td style='width:150px;'>" . $_SESSION['lang']['norek'] . "</td>
                <td>" . $_SESSION['lang']['noktp'] . "</td>             
                <td>" . $_SESSION['lang']['nohp'] . "</td>      
      </thead>
      <tbody>";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()){
        $stream.="<tr class=rowcontent>
                         <td>" . $bar1->unit . "</td>                    
                           <td>" . $bar1->nama . "</td>
                           <td>" . $bar1->alamat . "</td>
                           <td>" . $bar1->desa . "</td>                               
                           <td>" . $bar1->kecamatan . "</td>
                           <td>" . $bar1->kabupaten . "</td>
                           <td>" . $bar1->namabank . "</td>  
                           <td>" . $bar1->namapemilikrek . "</td>  
                           <td>" . $bar1->norek . "</td>            
                           <td>" . $bar1->noktp . "</td>  
                           <td>" . $bar1->hp . "</td>                                 
                           </tr>";
        }
        $stream.="	 
         </tbody>
         <tfoot>
         </tfoot>
         </table><br>";

        $stream.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $qwe = date("YmdHms");
        $nop_ = "Daftar_Masyarakat_" . $unitbawah . " " . $qwe;
        if (strlen($stream) > 0) {
            $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
            gzwrite($gztralala, $stream);
            gzclose($gztralala);
            echo "<script language=javascript1.2>
        window.location='tempExcel/" . $nop_ . ".xls.gz';
        </script>";
        }
        exit;

        break;
    case 'showupload':
		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
		}
	
        $tab = "";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
       
        @$lxxx.="NO. KTP";
        @$lyyy.="NAMA";
        
        $tab.="<tr>
        <td>
        <label id='ptupload' style='display:none'>".$pt."</label>
        </td>
        </tr>
        <tr>
        <td>".$lxxx."</td>
        <td>:</td>
        <td>
        <label id='xxx' style='font-weight:bold'>".$xxx."</label>
        </td>
        </tr>
        <tr>
        <td>".$lyyy."</td>
        <td>:</td>
        <td>
        <label id='yyy' style='display:none'>".$yyy."</label>
        <label id='yyyxx' style='font-weight:bold'>".$yyy."</label>
        </td>
        </tr>";
        $tab.="<tr><td colspan=4><hr></td></tr>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
            </tr>
            <tr>
            <td>Filename</td>
            <td>:</td>
            <td>
            <input type='file' name='upload' id='upload' >
            </td>
            </tr>
            <tr>
            <td colspan=2></td>
            <td>
            <button class=mybutton onclick=\"submitfile('".$jenisupload."')\">Submit</button>
            </td>
            </tr>
            </table>
            <p />";
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
            <thead>
            <tr class=rowheader>
            <td align='center' width=50px>No.</td>
            <td align='center'>Kriteria</td>
            <td align='center' width=50px>File Type</td>
            <td align='center'>Filename</td>
            <td align='center' width=50px>Action</td>
            </tr>
            </thead>
            <tbody id='listfiles'>
            </tbody>
            </table>
            </fieldset> ";
        echo $tab;
    break;
    case 'submitfile':
    $tgl = date("YmdHis");
    $his = date("His");
    $data = $_POST;
    if ($data['fileupload'] != '') {
        if ($_FILES['file']['error'] == 0) {
            $filetype = strtolower('.'.substr($_FILES['file']['name'], strripos($_FILES['file']['name'], '.') + 1));
            $filename = $pt."_".$xxx."_".$his."_".$_FILES['file']['name'];
            $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
            if (($filetype == '.jpeg') || ($filetype == '.jpg') || ($filetype == '.png') || ($filetype == '.pdf') || ($filetype == '.xls') || ($filetype == '.xlsx') || ($filetype == '.doc') || ($filetype == '.docx')) {
                if ($_FILES['file']['size'] <= 250000000) {
                    $str = "insert into ".$dbname.".listfile_lgl_grltt values ('','','".$jenisupload."','".$pt."','".$xxx."','".$yyy."','".$filename."','".$kriteria."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                    try {
                        $owlPDO->exec($str);
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        file_put_contents($path.$filename, $file_tmpname);
                    } catch (PDOException $e) {
                        echo " Gagal,".addslashes($e->getMessage());
                    }
                } else {
                    exit("warning : Ukuran file upload maksimal 250kb");
                }
            } else {
                exit("Warning : Format file upload harus .jpg atau .jpeg");
            }
        }
    }
    break;
    case 'loadfiles':
    $no = 0;
    $tab = $icon = "";
    $str = "select * from ".$dbname.".listfile_lgl_grltt where status='1' and jenis='".$jenisupload."' and field1='".$pt."' and field2='".$xxx."' and field3='".$yyy."'";
    //exit('error'.$str);
    $res = fetchData($str);
    if (empty($res)) {
        $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
    } else {
        foreach($res as $key => $val) {
			$optkriteria="";
			$arrmodul = getmodulefil($emodul);
			foreach($arrmodul as $keyx=>$valx){
				if($keyx==$val['kriteriaefil']){
					$optkriteria.="<option value='".$keyx."' selected>".$valx['kriteria']."</option>";
				}else{
					$optkriteria.="<option value='".$keyx."'>".$valx['kriteria']."</option>";
				}
			}
			
            $no++;
            $tab.="<tr class=rowcontent>
                <td style='text-align:center'>".$no."</td>";
			$tab.="<td style='text-align:center'>
                <label style='display:none'>".getcriterianame($val['kriteriaefil'])."</label>
				<select id='kriteriax_".$val['id']."' onchange=\"changekriteria('".$val['id']."')\">". $optkriteria."</select>
                </td>";
            $icon = seticonfile($val['formaticon']);
            $tab.="<td style='text-align:center'>
                <a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
                </td>";
            $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$val['namafile']."</td>
                <td align=center>
                <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
            $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['jenis']."','".$val['field1']."','".$val['field2']."','".$val['field3']."','".$val['namafile']."');\" >";
            $tab."  </td>
            </tr>";
        }
    }
    echo $tab;
    break;
	
	case'changekriteria':
		$str="update ".$dbname.".listfile_lgl_grltt set kriteriaefil='".$kriteriax."' where id='".$id."'";
		$owlPDO->exec($str);
	break;
    
    case 'viewfile':
    $tab = "";
    $tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
    echo $tab;
    break;
    case 'deletefile':
    $str = "delete from ".$dbname.".listfile_lgl_grltt where jenis='".$jenisupload."' and field1='".$pt."' and field2='".$xxx."' and field3='".$yyy."' and namafile='".$namafile."'"; //exit('error'.$str);
    try {
        $owlPDO->exec($str);
        $pathx = $path.$namafile;
        unlink($pathx);
    } catch (PDOException $e) {
        echo " Gagal,".addslashes($e->getMessage());
    }
    break;
    case 'update':
        $str = "update " . $dbname . ".pad_5masyarakat 
           set nama='" . $nama . "',
            alamat='" . $alamat . "',
            desa='" . $desa . "',               
            kecamatan='" . $kecamatan . "',
            kabupaten='" . $kabupaten . "',
            noktp='" . $ktp . "',
             hp='" . $hp . "',
             kodebank='" . $kodebank . "',
            namapemilikrek='" . $namapemilikrek . "',
             norek='" . $norek . "'
            where padid=" . $pid;
        #exit("error :".$str);
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'insert':
        $str = "insert into " . $dbname . ".pad_5masyarakat (nama,alamat,desa,kecamatan,kabupaten,noktp,hp,kodebank,namapemilikrek,norek)
              values('" . $nama . "','" . $alamat . "','" . $desa . "','" . $kecamatan . "','" . $kabupaten . "','" . $ktp . "','" . $hp . "','" . $kodebank . "','" . $namapemilikrek . "','" . $norek . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
	
	case 'deletedata':
		$str="select * from ".$dbname.".pad_lahan where pemilik='".$pid."'";
		$res=fetchdata($str);
		$countitem = count($res);
		
		if($countitem > 0){
			exit("Gagal, Item tidak dapat dihapus karena sudah pernah dilakukan transaksi.");
		}else{
			$str = "delete from " . $dbname . ".pad_5masyarakat where padid='".$pid."'";
			try {
				$owlPDO->exec($str);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
        break;
	
    case 'delete':
        $strjlh = "select * from " . $dbname . ".pad_lahan where pemilik = '".$pid."'";
		$res = fetchData($strjlh);
		if(count($res)>0){
			exit("Warning : Data tidak bisa di hapus, sudah ada transaksi !!!");
		}
		
		$str = "delete from " . $dbname . ".pad_5masyarakat where padid='" . $pid . "'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
    case 'loaddata':
		$limit = 20;
		$page = 0;
		$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}

		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);
		$no = 0;
		$tab = "";
		$no = $maxdisplay;
		$tab='';
		$where="";
        if ($idcari != '') {
            $where.=" and a.padid like '%" . $idcari . "%' ";
        }
		if ($namacari != '') {
            $where.=" and a.nama like '%" . $namacari . "%'";
        }
		if ($alamatcari != '') {
            $where.=" and a.alamat like '%" . $alamatcari . "%' ";
        }
		if ($ktpcari != '') {
            $where.=" and a.noktp like '%" . $ktpcari . "%' ";
        }

        $tabs="<table class=sortable cellspacing=1 border=0 width=100%>
         <thead>
                 <tr class=rowheader>              
                    <td align=center style='width:30px;'>No</td>                    
                    <td align=center style='width:30px;'>ID</td>                    
                    <td align=center>" . $_SESSION['lang']['nama'] . "</td>                    
                    <td align=center>" . $_SESSION['lang']['alamat'] . "</td>                        
                    <td align=center>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['desa'] . "</td>
                    <td align=center>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['kecamatan'] . "</td>                  
                    <td align=center>" . $_SESSION['lang']['kabupaten'] . "</td>    
                    <td align=center>" . $_SESSION['lang']['namabank'] . " </td>
                    <td align=center>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['pemilik'] . " " . $_SESSION['lang']['rekening'] . "</td> 
                    <td align=center>" . $_SESSION['lang']['norek'] . "</td>    
                    <td align=center>" . $_SESSION['lang']['noktp'] . "</td>             
                    <td align=center>" . $_SESSION['lang']['nohp'] . "</td>              
                    <td align=center> File </td>                      
                   <td align=center style='width:50px;'>Action</td></tr>    
          </thead>
          <tbody>";
		$strjlh = "select a.* from " . $dbname . ".pad_5masyarakat a
                left join " . $dbname . ".keu_5daftarbank c on a.kodebank=c.kodebank where 1=1 ".$where." 
                 order by a.desa,a.nama";  
		$res = fetchData($strjlh);
		$jlhbrs = count($res);
        $str1 = "select * from " . $dbname . ".pad_5masyarakat a
                left join " . $dbname . ".keu_5daftarbank c on a.kodebank=c.kodebank where 1=1 ".$where." 
                 order by a.padid desc,a.nama, a.desa limit " . $offset . "," . $limit . "";
        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
			$no++;
			$arrkabupaten=makeOption($dbname, 'kabupaten', 'id,kabupaten',"id='".$bar1->kabupaten."'");
			$arrkecamatan=makeOption($dbname, 'kecamatan', 'idkec,kecamatan',"idkec='".$bar1->kecamatan."'");
			$arrdesa=makeOption($dbname, 'desa', 'iddes,desa',"iddes='".$bar1->desa."' and id_kec='".$bar1->kecamatan."'");
            $tabs.="<tr class=rowcontent>            
				   <td align=center>" . $no. "</td>
				   <td align=center>" . $bar1->padid . "</td>
				   <td>" . $bar1->nama . "</td>
				   <td>" . $bar1->alamat . "</td>
				   <td>" . $arrdesa[$bar1->desa] . "</td>                               
				   <td>" . $arrkecamatan[$bar1->kecamatan] . "</td>
				   <td>" . $arrkabupaten[$bar1->kabupaten] . "</td>  
				   <td>" . $bar1->namabank. "</td>  
				   <td>" . $bar1->namapemilikrek . "</td>  
				   <td>" . $bar1->norek . "</td>     
				   <td>" . $bar1->noktp . "</td>  
				   <td>" . $bar1->hp . "</td>";

					$str = "select * from ".$dbname.".listfile_lgl_grltt where status='1' and jenis='datadiri' and field1='".$bar1->padid."' and field2='".$bar1->noktp."' and field3='".$bar1->nama."'";

					$res = fetchData($str);
					if (empty($res)) {
					   $tabs.="<td style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>"; 
					} else {
						$namafile='';
						$nox=0;
						foreach($res as $key => $val) {
							$nox++;
							if($namafile==''){
								$namafile=$nox.".".$val['namafile'];
							}else{
								$namafile.="<br>".$nox.".".$val['namafile'];
							}
						}
						$tabs.="<td style='text-align:left'>".$namafile."</td>";
					}                               
				$tabs.="<td align=center>
					<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . 	$bar1->padid . "','" . $bar1->nama . "','" . $bar1->alamat . "','" . $bar1->desa . "','" . $bar1->kecamatan . "','" . $bar1->kabupaten . "','" . $bar1->noktp . "','" . $bar1->hp . "','" . $bar1->kodebank . "','" . $bar1->namapemilikrek . "','" . $bar1->norek . "');\">
					
					<img src='images/skyblue/delete.png' class='resicon' onclick=\"deleteData('" . $bar1->padid . "');\" title='Delete'>
					
				   <img src='images/upload-2-xxl.png' class=zImgBtn title='Upload Document' onclick=\"showupload(event,'datadiri','".$bar1->padid."','" . $bar1->noktp . "','" . $bar1->nama . "')\">
					</td>
			</tr>";
        }
		
		$tab.="<tr>";
		$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $tabs.="</tr><tr><td colspan=27 align=center>";
        if ($page == '0') {
            $tabs.="<button class=mybutton disabled=true>Prev</button>";
        } else {
            $tabs.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>Prev</button>";
        }

        $tabs.="<select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"getPage()\">" . $isiRow . "</select>";

        if (($page + 1) == $totrows) {
            $tabs.="<button class=mybutton disabled=true>Next</button>";
        } else {
            $tabs.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>Next</button>";
        }
        $tabs.="</td>
            </tr>";
			
        $tabs.="  
         </tbody>
         <tfoot>
         </tfoot>
         </table>";
         echo $tabs;
    break;
    default:
        break;
}

?>
