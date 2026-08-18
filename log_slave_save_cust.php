<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param=$_POST;
if($_GET['proses']!=''){
    $param=$_GET;
}

$kodecustomer = checkPostGet('kodecustomer','');
$namacustomer = checkPostGet('namacustomer','');
$inisialcustomer = checkPostGet('inisialcustomer','');
$alamat = checkPostGet('alamat','');
$kota = checkPostGet('kota','');
$telepon = checkPostGet('telepon','');
$kontakperson = checkPostGet('kontakperson','');
$akun = checkPostGet('akun','');
$plafon = checkPostGet('plafon','');
$nilaihutang = checkPostGet('nilaihutang','');
$npwp = checkPostGet('npwp','');
$npwpalamat = checkPostGet('npwpalamat','');
$penandatangan = checkPostGet('penandatangan','');
$jabatan = checkPostGet('jabatan','');
$noseri = checkPostGet('noseri','');
$klcustomer = checkPostGet('klcustomer','');
$method = checkPostGet('method','');
$komoditi = checkPostGet('komoditi','');
$berikat = checkPostGet('berikat','');
$ketBerikat = checkPostGet('ketBerikat','');
$toleransipenyusutan = checkPostGet('toleransipenyusutan','');
$statusinteks = checkPostGet('statusinteks','');
$penjualan = checkPostGet('penjualan','');
$jenispph = checkPostGet('jenispph','');
$pphpersen = checkPostGet('pphpersen','');
$carabayar = checkPostGet('carabayar','');
$statusbebas = checkPostGet('statusbebas','');
// exit("Error:$statusbebas");
$jenispenghasilan = checkPostGet('jenispenghasilan','');
$namacustomer = checkPostGet('namacustomer','');
$strx = "";
$arrX = array('franco' => 'Franco', 'loco' => 'Loco', 'fob' => 'FOB');
$arrberikatbebas=array("0"=>"","1"=>"✓");


# Nama Akun
$namaakun = makeOption($dbname,"keu_5akun","noakun,namaakun");

//print_r($_POST);
// echo $method;exit("Error:MASUK");
switch ($method) {
	case 'showformakunpajak':
		$tab = "";

		$sql = selectQuery($dbname,"setup_parameterappl","*","kodeparameter='PMNPJK' AND kodeaplikasi='PJ'");
		$res = fetchData($sql);

		foreach($res as $val) {
			$arrakunx = $val['nilai'];
		}

		$arrakunexpl = explode(",",$arrakunx);

		$optakunap = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

		foreach($arrakunexpl as $key => $val) {
			$optakunap .= "<option value='".$val."'>".$val." - ".$namaakun[$val]."</option>";
		}
		

		$tab .= "<table style='width=100%;margin:8% auto;' border=0 cellpadding=1 cellspacing=1>";
			$tab .= "<tbody>";
				$tab .= "<tr>";
					$tab .= "<td style='width:30%'>".$_SESSION['lang']['kodecustomer']."</td>";
					$tab .= "<td style='width:10%'>:</td>";
					$tab .= "<td style='width:60%'><input id='kodecustap' style='width:195px;' type='text' value='".$param['customer_detail']."' class=myinputtext disabled /></td>";
				$tab .= "</tr>";
				
				$tab .= "<tr>";
					$tab .= "<td style='width:30%'>".$_SESSION['lang']['noakun']."</td>";
					$tab .= "<td style='width:10%'>:</td>";
					$tab .= "<td style='width:60%'><select style='width:200px;' id=noakunap>".$optakunap."</select></td>";
				$tab .= "</tr>";
				
				$tab .= "<tr>";
					$tab .= "<td style='width:30%'>".$_SESSION['lang']['persen']." PPn / PPh</td>";
					$tab .= "<td style='width:5%'>:</td>";
					$tab .= "<td style='width:65%'><input id=tarifap style='width:195px;' type=text class=myinputtextnumber /></td>";
				$tab .= "</tr>";
				
				$tab .= "<tr>";
					$tab .= "<td colspan=3 style='width:100%'><button style='width:100%;margin-top:20px;padding:4px 12px;cursor:pointer;' onclick='savePajak()'>".$_SESSION['lang']['save']."</button</td>";
				$tab .= "</tr>";
			$tab .= "</tbody>";
		$tab .= "</table>";

		$tab .= "<div id='loaddatadetailpajak'></div>";

		echo $tab;
	break;

	case 'loaddatadetailpajak':
		$tab = "";

		$sql = selectQuery($dbname,"pmn_5akunpajak","*","kodecustomer='".$param['customer_detail']."'");
		$res = fetchData($sql,"OBJECT");

		$tab .= "<table width=100% border=0 cellpadding=1 cellspacing=1 class=sortable>";
			$tab .= "<thead>";
				$tab .= "<tr>";
					$tab .= "<th>No</th>";
					$tab .= "<th>".$_SESSION['lang']['kodepelanggan']."</th>";
					$tab .= "<th>".$_SESSION['lang']['noakun']."</th>";
					$tab .= "<th>".$_SESSION['lang']['persen']." PPn / PPh</th>";
					$tab .= "<th colspan=2>".$_SESSION['lang']['aksi']."</th>";
				$tab .= "</tr>";
			$tab .= "</thead>";

			$tab .= "<tbody>";
				$No = 0;
				foreach($res as $val):
					$No++;
					$tab .= "<tr class=rowcontent>";
						$tab .= "<td align=center>".$No."</td>";
						$tab .= "<td align=center>".$val->kodecustomer."</td>";
						$tab .= "<td align=center>".$val->noakun." - ".$namaakun[$val->noakun]."</td>";
						$tab .= "<td align=center>".$val->tarif."%</td>";
						$tab .= "<td style='vertical-align:top;' align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdetailpajak('" . $bar->kodecustomer . "','" . $bar->namacustomer . "','" . $bar->alamat . "','" . $bar->kota . "','" . $bar->telepon . "','','" . $bar->akun . "','" . $bar->plafon . "','" . $bar->nilaihutang . "','" . $bar->npwp . "','" . $bar->alamatnpwp . "','" . $bar->penandatangan . "','" . $bar->jabatan . "','" . $bar->noseri . "','" . $bar->klcustomer . "','" . (isset($bas->namaakun) ? $bas->namaakun : '') . "','','" . $bar->toleransipenyusutan . "','" . $bar->statusberikat . "','" . $bar->keteranganberikat . "','" . substr($hasilKomoditi, 1) . "','" . $bar->statusinteks . "','" . $bar->jenispph . "','" . $bar->pphpersen . "','" . $bar->carabayar . "','" . $bar->jenispenghasilan . "','" . $bar->statusbebas . "');\"></td>";
						$tab .= "<td style='vertical-align:top;' align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletedetailpajak('" . $val->kodecustomer . "','".$val->noakun."','".$val->tarif."');\"></td>";
					$tab .= "</tr>";
				endforeach;
			$tab .= "</tbody>";
		$tab .= "</table>";

		echo $tab;
	break;

	case 'savePajak':
		try {
			$owlPDO->beginTransaction();

			$str = "INSERT INTO {$dbname}.pmn_5akunpajak VALUES ('".$param['noakun_detail']."','".$param['persen_detail']."','".$param['customer_detail']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','','')";
            $owlPDO->exec($str);

			$owlPDO->commit();
        } catch (PDOException $e) {
			$owlPDO->rollback();

			print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;
	
	case 'deletePajak':
		try {
			$owlPDO->beginTransaction();

			$str = "DELETE FROM {$dbname}.pmn_5akunpajak WHERE noakun='".$param['noakun_detail']."' AND tarif='".$param['persen_detail']."' AND kodecustomer='".$param['customer_detail']."'";
            $owlPDO->exec($str);

			$owlPDO->commit();
        } catch (PDOException $e) {
			$owlPDO->rollback();

			print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
	break;

    case 'delete':
        $str1 = "delete from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "'";
        $str2 = "delete from " . $dbname . ".pmn_4customercontact where kodecustomer='" . $kodecustomer . "'";
        $str3 = "delete from " . $dbname . ".pmn_4komoditi where kodecustomer='" . $kodecustomer . "'";
        // $str4 = "delete from " . $dbname . ".log_5supplier where kodecustomer='" . $kodecustomer . "'";
		
        try {
            $owlPDO->exec($str1);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        try {
            $owlPDO->exec($str2);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		try {
            $owlPDO->exec($str3);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		
		// try {
        //     $owlPDO->exec($str4);
        // } catch (PDOException $e) {
        //     print " Gagal  !: " . $e->getMessage() . "\n";
        //     die();
        // }

	break;
    case 'update':
        //print_r($_POST); exit();
        $sKo = "delete from " . $dbname . ".pmn_4komoditi where kodecustomer='" . $kodecustomer . "'";
        try {
            $owlPDO->exec($sKo);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
		$nourut=0;
        $expKomoditi = explode(",", $komoditi);
        foreach ($expKomoditi as $key) {
			$nourut++;
			$optInis = makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kodebarang='".$key."'");
            $sUKo = "insert into " . $dbname . ".pmn_4komoditi (kodecustomer,kodebarang,kodekomoditi) values ('" . $kodecustomer . "','" . $key . "','".$optInis[$key]."')";
            try {
                $owlPDO->exec($sUKo);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        $strx = "update " . $dbname . ". pmn_4customer set namacustomer='" . $namacustomer . "',inisialcustomer='" . $inisialcustomer . "',alamat='" . $alamat . "',kota='" . $kota . "',
        telepon='" . $telepon . "',
        akun='" . $akun . "',plafon='" . $plafon . "',jenispph='" . $jenispph . "',pphpersen='" . $pphpersen . "',carabayar='" . $carabayar . "',jenispenghasilan='" . $jenispenghasilan . "',
        nilaihutang='" . $nilaihutang . "',npwp='" . $npwp . "',alamatnpwp='" . $npwpalamat . "'
        ,penandatangan='" . $penandatangan . "',jabatan='" . $jabatan . "'
        ,noseri='" . $noseri . "',klcustomer='" . $klcustomer . "', penjualan='" . $penjualan . "', statusinteks='" . $statusinteks . "' ,
		toleransipenyusutan='" . $toleransipenyusutan . "',statusberikat='" . $berikat . "',keteranganberikat='" . $ketBerikat . "',
		updateby='" . $_SESSION['standard']['userid'] . "',statusbebas='" . $statusbebas . "'
        where kodecustomer='" . $kodecustomer . "'";
        try {
            $owlPDO->exec($strx);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;

        case 'savefile':

		$tgl = date("YmdHis");
		$data = $_POST;
		// print_r($data);exit("Error:A");
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name']; 
				$path="fileupload/customer/".basename($_FILES['file']['name']);
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 512000){
						$strx="update ".$dbname.".pmn_4customer set namafile='".$filename."' where kodecustomer='".$kodecustomer."' ";
						$owlPDO->exec($strx);
						move_uploaded_file($file_tmpname,"fileupload/customer/$filename");
						}else{
						exit("warning : Ukuran file upload maksimal 512 kb");
					}
				}else{
					exit("Warning : Format file upload harus doc, docx, .png, .jpg atau .jpeg");
				}
			}
		}
    break;

    case 'savefilelegal':

		$tgl = date("YmdHis");
		$data = $_POST;
		// print_r($data);exit("Error:A");
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name']; 
				$path="fileupload/customer/".basename($_FILES['file']['name']);
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 512000){
						$strx="update ".$dbname.".pmn_4customer set namafilelegal='".$filename."' where kodecustomer='".$kodecustomer."' ";
						$owlPDO->exec($strx);
						move_uploaded_file($file_tmpname,"fileupload/customer/$filename");
						}else{
						exit("warning : Ukuran file upload maksimal 512 kb");
					}
				}else{
					exit("Warning : Format file upload harus doc, docx, .png, .jpg atau .jpeg");
				}
			}
		}
    break;

    case 'insert':
        $expKomoditi = explode(",", $komoditi);
		$nourut=0;
        foreach ($expKomoditi as $key) {
			$nourut++;
			$optInis = makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kodebarang='".$key."'");
            $sKo = "insert into " . $dbname . ".pmn_4komoditi (kodecustomer,kodebarang,kodekomoditi) values ('" . $kodecustomer . "','" . $key . "','".$optInis[$key]."')";
			try {
                $owlPDO->exec($sKo);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }


        $strx = "insert into " . $dbname . ".pmn_4customer
		(`kodecustomer`, `inisialcustomer`,`namacustomer`, `alamat`, `kota`, `telepon`,
		`akun`, `plafon`, `nilaihutang`, `npwp`, `alamatnpwp`, 
		`penandatangan`, `jabatan`, `noseri`, `klcustomer`, `toleransipenyusutan`, 
		`statusberikat`, `keteranganberikat`,`penjualan`,`statusinteks`, `jenispph`,
		`pphpersen`,`carabayar`,`jenispenghasilan`,`createby`,`createtime`,
		`statusbebas`)
		values
		('" . $kodecustomer . "','" . $inisialcustomer . "','" . $namacustomer . "','" . $alamat . "','" . $kota . "','" . $telepon . "',
		'" . $akun . "','" . $plafon . "','" . $nilaihutang . "','" . $npwp . "','" . $npwpalamat . "',
		'" . $penandatangan . "','" . $jabatan . "','" . $noseri . "','" . $klcustomer . "','" . $toleransipenyusutan . "',
		'" . $berikat . "','" . $ketBerikat . "','" . $penjualan . "','" . $statusinteks . "','" . $jenispph . "',
		'" . $pphpersen . "','" . $carabayar . "','" . $jenispenghasilan . "','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."',
		'".$statusbebas."')"; 

        try {
            $owlPDO->exec($strx);

        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        break;
		
	case'loaddata':
	 //ambil data dari tabel kelompok customer
		
		$where='where 1=1';
		if($namacustomer!=''){
			$where.=" and namacustomer like '%".$namacustomer."%' ";
			
		}
		
		
		$srt = "select * from " . $dbname . ".pmn_4customer  ".$where." order by kodecustomer desc";  //echo $srt;
		//if($rep=mysql_query($srt))
		// {
		$no = 0;
		//while($bar=mysql_fetch_object($rep))
		$rep = $owlPDO->query($srt) or die(print " Gagal: " . PDOException::getMessage());
		$rep->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $rep->fetch()) {
			//get kelompok cust
			$sql = "select * from " . $dbname . ".pmn_4klcustomer where `kode`='" . $bar->klcustomer . "'";
			//$query=mysql_query($sql) or die(mysql_error($conn));
			//$res=mysql_fetch_object($query);
			$query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_OBJ);
			$res = $query->fetch();

			//get Komoditi
			$sKo = "select t1.*,t2.namabarang from " . $dbname . ".pmn_4komoditi t1
						left join " . $dbname . ".log_5masterbarang t2
						on t1.kodebarang = t2.kodebarang
						where `kodecustomer`='" . $bar->kodecustomer . "'";
			//$qKo=mysql_query($sKo) or die(mysql_error($conn));
			$hasilKomoditi = "";
			$hasilKomoditi2 = "";
			//while($rKo=mysql_fetch_object($qKo)){
			$qKo = $owlPDO->query($sKo) or die(print " Gagal: " . PDOException::getMessage());
			$qKo->setFetchMode(PDO::FETCH_OBJ);
			while ($rKo = $qKo->fetch()) {
				$hasilKomoditi.="," . $rKo->kodebarang;
				$hasilKomoditi2.=",<br>" . $rKo->namabarang;
			}

			//get Kontak Person
			$sPer = "select * from " . $dbname . ".pmn_4customercontact
						where `kodecustomer`='" . $bar->kodecustomer . "'";
			//$qPer=mysql_query($sPer) or die(mysql_error($conn));
			$hasilPerson = "";
			//while($rPer=mysql_fetch_object($qPer))
			$qPer = $owlPDO->query($sPer) or die(print " Gagal: " . PDOException::getMessage());
			$qPer->setFetchMode(PDO::FETCH_OBJ);
			while ($rPer = $qPer->fetch()) {
				$hasilPerson.=",<br>" . $rPer->nama . " (" . $rPer->email . ")";
			}

			//get akun
			$spr = "select * from  " . $dbname . ".keu_5akun where `noakun`='" . $bar->akun . "'";
			//$rej=mysql_query($spr) or die(mysql_error($conn));
			//$bas=mysql_fetch_object($rej);
			$rej = $owlPDO->query($spr) or die(print " Gagal: " . PDOException::getMessage());
			$rej->setFetchMode(PDO::FETCH_OBJ);
			$bas = $rej->fetch();
			$no++;
			$bar->alamat = clearInvalidChar($bar->alamat);
			$bar->telepon = clearInvalidChar($bar->telepon);
			$bar->keteranganberikat = clearInvalidChar($bar->keteranganberikat);
			echo"<tr class=rowcontent>
					<td style='vertical-align:top;'  align=center>" . $no . "</td>
					<td style='vertical-align:top;'>" . substr($hasilKomoditi2, 5) . "</td>
					<td style='vertical-align:top;' align=center>" . $bar->kodecustomer . "</td>
					<td style='vertical-align:top;'>" . $bar->namacustomer . "</td>
					<td style='vertical-align:top;' align=center>" . $bar->inisialcustomer . "</td>
					<td style='vertical-align:top;'>" . $bar->alamat . "</td>
					<td style='vertical-align:top;'>" . $bar->kota . "</td>
					<td style='vertical-align:top;'>" . $bar->telepon . "</td>
					<td style='vertical-align:top;'>" . $bar->npwp . "</td>
					<td style='vertical-align:top;'>" . $bar->alamatnpwp . "</td>
					<td style='vertical-align:top;'>" . $bar->penandatangan . "</td>
					<td style='vertical-align:top;'>" . $bar->jabatan . "</td>
					<td style='vertical-align:top;'>" . substr($hasilPerson, 5) . "</td>
					<!--<td style='vertical-align:top;'>" . $arrX[$bar->penjualan] . "</td>-->
					<td style='vertical-align:top;'  display:none'>" . $bar->statusinteks . "</td>
					<td style='vertical-align:top; text-align:right; display:none'>" . $bar->plafon . "</td>
					<td style='vertical-align:top; text-align:right; display:none'>" . $bar->nilaihutang . "</td>
					<td style='vertical-align:top; text-align:right; display:none'>" . $bar->toleransipenyusutan . "</td>
					
					
					<td style='vertical-align:top;' align=center>" . $arrberikatbebas[$bar->statusberikat] . "</td>
					<td style='vertical-align:top;' align=center  display:none'>" . $arrberikatbebas[$bar->statusbebas] . "</td>
					
					<!--<td style='vertical-align:top;'>" . $bar->keteranganberikat . "</td>-->
					<td style='vertical-align:top;' align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('" . $bar->kodecustomer . "','" . $bar->inisialcustomer . "','" . $bar->namacustomer . "','" . $bar->alamat . "','" . $bar->kota . "','" . $bar->telepon . "','','" . $bar->akun . "','" . $bar->plafon . "','" . $bar->nilaihutang . "','" . $bar->npwp . "','" . $bar->alamatnpwp . "','" . $bar->penandatangan . "','" . $bar->jabatan . "','" . $bar->noseri . "','" . $bar->klcustomer . "','" . (isset($bas->namaakun) ? $bas->namaakun : '') . "','','" . $bar->toleransipenyusutan . "','" . $bar->statusberikat . "','" . $bar->keteranganberikat . "','" . substr($hasilKomoditi, 1) . "','" . $bar->statusinteks . "','" . $bar->jenispph . "','" . $bar->pphpersen . "','" . $bar->carabayar . "','" . $bar->jenispenghasilan . "','" . $bar->statusbebas . "');\"></td>
					<td style='vertical-align:top;' align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPlgn('" . $bar->kodecustomer . "');\"></td>
					<td style='vertical-align:top;' align=center><img src=images/plus.png class=resicon  title='File NPWP' onclick=\"detaildt('" . $bar->kodecustomer . "');\"></td>
					<td style='vertical-align:top;' align=center><img src=images/plus.png class=resicon  title='File Legalitas' onclick=\"detaildtlegal('" . $bar->kodecustomer . "');\"></td>
					<td style='vertical-align:top;' align=center><img src=images/book_icon.gif class=resicon  title='Pajak' onclick=\"showAkunPajak('" . $bar->kodecustomer . "');\"></td>
				</tr>";
		}
	break;	
		
		
	case'loaddataxxx':
		$srt = "select * from " . $dbname . ".pmn_4customer order by kodecustomer desc";  //echo $srt;
		$no = 0;
		$rep = $owlPDO->query($srt) or die(print " Gagal: " . PDOException::getMessage());
		$rep->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $rep->fetch()) {
			//get kelompok cust
			$sql = "select * from " . $dbname . ".pmn_4klcustomer where `kode`='" . $bar->klcustomer . "'";
			$query = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
			$query->setFetchMode(PDO::FETCH_OBJ);
			$res = $query->fetch();


			//get Komoditi
			$sKo = "select t1.*,t2.namabarang from " . $dbname . ".pmn_4komoditi t1
						left join " . $dbname . ".log_5masterbarang t2
						on t1.kodebarang = t2.kodebarang
						where `kodecustomer`='" . $bar->kodecustomer . "'";
			$hasilKomoditi = "";
			$hasilKomoditi2 = "";
			$qKo = $owlPDO->query($sKo) or die(print " Gagal: " . PDOException::getMessage());
			$qKo->setFetchMode(PDO::FETCH_OBJ);
			while ($rKo = $qKo->fetch()) {
				$hasilKomoditi.="," . $rKo->kodebarang;
				$hasilKomoditi2.=",<br>" . $rKo->namabarang;
			}

			//get Kontak Person
			$sPer = "select * from " . $dbname . ".pmn_4customercontact
						where `kodecustomer`='" . $bar->kodecustomer . "'";
			$hasilPerson = "";
			$qPer = $owlPDO->query($sPer) or die(print " Gagal: " . PDOException::getMessage());
			$qPer->setFetchMode(PDO::FETCH_OBJ);
			while ($rPer = $qPer->fetch()) {
				$hasilPerson.=",<br>" . $rPer->nama . " (" . $rPer->email . ")";
			}
			//get akun
			$spr = "select * from  " . $dbname . ".keu_5akun where `noakun`='" . $bar->akun . "'";
			$rej = $owlPDO->query($spr) or die(print " Gagal: " . PDOException::getMessage());
			$rej->setFetchMode(PDO::FETCH_OBJ);
			$bas = $rej->fetch();
			$no+=1;
			$bar->alamat = clearInvalidChar($bar->alamat);
			$bar->telepon = clearInvalidChar($bar->telepon);
			$bar->keteranganberikat = clearInvalidChar($bar->keteranganberikat);
			echo"<tr class=rowcontent>
						<td style='vertical-align:top;'   align=center>" . $no . "</td>
						<td style='vertical-align:top;'>" . substr($hasilKomoditi2, 5) . "</td>
						<td style='vertical-align:top;'  align=center>" . $bar->kodecustomer . "</td>
						<td style='vertical-align:top;'>" . $bar->namacustomer . "</td>
						<td style='vertical-align:top;'>" . $bar->alamat . "</td>
						<td style='vertical-align:top;'>" . $bar->telepon . "</td>
						<td style='vertical-align:top;'>" . $bar->npwp . "</td>
						<td style='vertical-align:top;' align=center>" . $arrberikatbebas[$bar->statusberikat] . "</td>
						<td style='vertical-align:top;' align=center>" . $arrberikatbebas[$bar->statusbebas] . "</td>
						<td style='vertical-align:top;'>" . $bar->penandatangan . "<br>" . $bar->jabatan . "</td>
						<td style='vertical-align:top;'>" . substr($hasilPerson, 5) . "</td>
						  <td style='vertical-align:top;'>" . $bar->statusinteks . "</td>
						<td style='vertical-align:top; align=center;'><img src=images/application/application_edit.png class=resicon  title='Edit' 
						onclick=\"fillField('" . $bar->kodecustomer . "','" . $bar->namacustomer . "','" . $bar->alamat . "','" . $bar->kota . "','" . $bar->telepon . "',
						'','" . $bar->akun . "','" . $bar->plafon . "','" . $bar->nilaihutang . "','" . $bar->npwp . "',
						'" . $bar->alamatnpwp . "','" . $bar->penandatangan . "','" . $bar->jabatan . "','" . $bar->noseri . "','" . $bar->klcustomer . "',
						'" . (isset($bas->namaakun) ? $bas->namaakun : '') . "','','" . $bar->toleransipenyusutan . "','" . $bar->statusberikat . "','" . $bar->keteranganberikat . "',
						'" . substr($hasilKomoditi, 1) . "','" . $bar->penjualan . "','" . $bar->statusinteks . "','" . $bar->jenispph . "','" . $bar->pphpersen . "',
						'" . $bar->carabayar . "','" . $bar->jenispenghasilan . "','" . $bar->statusbebas . "');\"></td>
						<td style='vertical-align:top;'  align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPlgn('" . $bar->kodecustomer . "');\"></td>
						</tr>";//<td style='vertical-align:top;' align=center><img src=images/plus.png class=resicon  title='Upload' onclick=\"detaildt('" . $bar->kodecustomer . "');\"></td>
						
		}
		
	break;	
		
		
		
    default:
        break;
}




?>
