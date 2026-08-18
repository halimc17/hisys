<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$unitbawah = checkPostGet('unitbawah', '');
$provinsi = checkPostGet('provinsi', '');
$handil = checkPostGet('handil', '');
$desa = checkPostGet('desa', '');
$kecamatan = checkPostGet('kecamatan', '');
$kabupaten = checkPostGet('kabupaten', '');
$handilcari = checkPostGet('handilcari', '');
$desacari = checkPostGet('desacari', '');


switch ($method) {
    case 'excel':
        $stream = "";
        $str1 = "select * from " . $dbname . ".pad_5desa where unit like '" . $unitbawah . "%' order by desa";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        if (owlBaris($res1)!=0){
            $stream.="<table class=sortable cellspacing=1 border=1 style='width:500px;'>
    <thead><tr bgcolor='#dedede'>
        <td style='width:150px;'>" . $_SESSION['lang']['kodeorg'] . "</td>
        <td>" . $_SESSION['lang']['handil'] . "</td> 
        <td>" . $_SESSION['lang']['provinsi'] . "</td>
        <td>" . $_SESSION['lang']['kabupaten'] . "</td>
        <td>" . $_SESSION['lang']['kecamatan'] . "</td> 
        <td>" . $_SESSION['lang']['desa'] . "</td>    
    </thead>
    <tbody>";
            while ($bar1 = $res1->fetch()) {
                $stream.="<tr class=rowcontent>
        <td align=center>" . $bar1->unit . "</td>
        <td>" . $bar1->handil . "</td>
        <td>" . $bar1->provinsi . "</td>
        <td>" . $bar1->kabupaten . "</td>
        <td>" . $bar1->kecamatan . "</td>
        <td>" . $bar1->desa . "</td>       
        </tr>";
            }
            $stream.="	 
    </tbody>
    <tfoot>
    </tfoot>
    </table><br>";
        }
        $stream.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $qwe = date("YmdHms");
        $nop_ = "Daftar_Desa_" . $unitbawah . " " . $qwe;
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
    case 'update':
        $str = "update " . $dbname . ".pad_5desa set unit='" . $unit . "',
						   desa='" . $desa . "',
                           kecamatan='" . $kecamatan . "',
                           kabupaten='" . $kabupaten . "',
                           provinsi='" . $provinsi . "'
               where handil='" . $handil . "'  ";
		//echo"$str";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'insert':
		if($handil==''){
			exit("Warning : Handil tidak boleh kosong !!!");
		}
		$str = "select * from ".$dbname.".pad_5desa where handil = '".$handil."'";		
		$res = fetchData($str);
		if(count($res)>0){
			exit("Warning : Handil sudah ada !!!");
		}
			
        $str = "insert into " . $dbname . ".pad_5desa (handil,provinsi,kabupaten,kecamatan,desa,unit)
              values('" . $handil . "','" . $provinsi . "','" . $kabupaten . "','" . $kecamatan . "','" . $desa . "','" . $unit . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".pad_5desa
        where desa='" . $desa . "' and handil='" . $handil . "' ";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    case 'getkabupaten':
        $arrkabupaten=makeOption($dbname, 'kabupaten', 'id,kabupaten',"id_prov='".$provinsi."'");
        $optkabupaten="<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
        foreach ($arrkabupaten as $ky => $vle) {
            $optkabupaten.="<option value='".$ky."'>".$vle."</option>";
        }
        echo $optkabupaten;
    break;
    case 'getkecamatan':
        $arrkecamatan=makeOption($dbname, 'kecamatan', 'idkec,kecamatan',"id_kab='".$kabupaten."'");
        $optkecamatan="<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
        foreach ($arrkecamatan as $ky => $vle) {
            $optkecamatan.="<option value='".$ky."'>".$vle."</option>";
        }
        echo $optkecamatan;
    break;
    case 'getdesa':
        $arrdesa=makeOption($dbname, 'desa', 'iddes,desa',"id_kec='".$kecamatan."'");
        $optdesa="<option value=''>" . $_SESSION['lang']['pilih'] . "</option>";
        foreach ($arrdesa as $ky => $vle) {
            $optdesa.="<option value='".$ky."'>".$vle."</option>";
        }
        echo $optdesa;
    break;
    case'loaddata':

        $where = '';
        if($handilcari != "" ){
            $where.= "and handil like '%".$handilcari."%'";
        }

		$str1 = "select * from " . $dbname . ".pad_5desa where unit like '" . $unitbawah . "%' ".$where." order by desa";
		echo"<table class=sortable cellspacing=1 border=0 style='width:980px;'>
			 <thead><tr class=rowheader>
						<td>" . $_SESSION['lang']['kodeorg'] . "</td>
						<td>" . $_SESSION['lang']['handil'] . "</td> 
						<td>" . $_SESSION['lang']['provinsi'] . "</td>
						<td>" . $_SESSION['lang']['kabupaten'] . "</td>
						<td>" . $_SESSION['lang']['kecamatan'] . "</td> 
						<td>" . $_SESSION['lang']['desa'] . "</td>    
						<td style='width:30px;' align=center>Action</td></tr>    
			  </thead>
			  <tbody>";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {
		$arrprov=makeOption($dbname, 'provinsi', 'id,provinsi',"id='".$bar1->provinsi."'");
		$arrkabupaten=makeOption($dbname, 'kabupaten', 'id,kabupaten',"id='".$bar1->kabupaten."'");
		$arrkecamatan=makeOption($dbname, 'kecamatan', 'idkec,kecamatan',"idkec='".$bar1->kecamatan."'");
		$arrdesa=makeOption($dbname, 'desa', 'iddes,desa',"iddes='".$bar1->desa."' and id_kec='".$bar1->kecamatan."'");

			echo"<tr class=rowcontent>
								   <td align=center>" . $bar1->unit . "</td>
								   <td>" . $bar1->handil . "</td>
								   <td>" . $arrprov[$bar1->provinsi] . "</td> 
								   <td>" . $arrkabupaten[$bar1->kabupaten] . "</td> 
								   <td>" . $arrkecamatan[$bar1->kecamatan] . "</td> 
								   <td>" . $arrdesa[$bar1->desa] . "</td>     
								   <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->unit . "','" . $bar1->handil . "','" . $bar1->provinsi . "','" . $bar1->kabupaten . "','" . $bar1->kecamatan . "','" . $bar1->desa . "');\">
									</td></tr>";
		}
		 echo"	 
		  </tbody>
		  <tfoot>
		  </tfoot>
		  </table>";
	
	break;
}


?>
