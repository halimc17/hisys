<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method     = checkPostGet('method', '');
$kodeorg    = checkPostGet('kodeorg', '');
$jenis      = checkPostGet('jenis', '');
$periode    = checkPostGet('periode', '');
$basis1     = checkPostGet('basis1', '');
$basis2     = checkPostGet('basis2', '');
$siapbasis  = checkPostGet('siapbasis', '');
$siapbasis2 = checkPostGet('siapbasis2', '');
$lebihbasis1= checkPostGet('lebihbasis1', '');
$lebihbasis2= checkPostGet('lebihbasis2', '');
$brondol    = checkPostGet('brondol', '');
$tidakbasis = checkPostGet('tidakbasis', '');
$ambiltt    = checkPostGet('ambiltt', '');
$tahuntanam = checkPostGet('tahuntanam', '');
$periodeke  = checkPostGet('periodeke', '');
$jeniske    = checkPostGet('jeniske', '');
$rphk       = checkPostGet('rphk', '');


$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
switch ($method) {
    case'copy':
        if($kodeorg=='' or $periode=='' or $periodeke==''){
            exit("Warning : Kode organisasi, Periode harus diisi.");
        }
        $wh="";
        if($jenis!=''){
            $wh=" and jenispremi='".$jenis."'";
        }
        
        $sql = "select * from " . $dbname . ".kebun_5basispanen3 where kodeorg='".$kodeorg."' ".$wh." and periode='".$periode."'";
        $res = fetchdata($sql);
        if(count($res)==0){
            exit("Warning : Data sumber tidak ada, proses dibatalkan.");
        }
        
        $str = "delete from " . $dbname . ".kebun_5basispanen3 where kodeorg='".$kodeorg."' ".$wh." and periode='".$periodeke."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        
        foreach($res as $bar){
            $data = array(
				'kodeorg'         => $kodeorg,
				'periode'         => $periodeke,
				'jenispremi'      => $bar['jenispremi'],
				'tahuntanam'      => $bar['tahuntanam'],
				'basis1'          => $bar['basis1'],
				'basis2'          => $bar['basis2'],
				'tidakbasis'      => $bar['tidakbasis'],
				'premibasis1'     => $bar['premibasis1'],
				'premibasis2'     => $bar['premibasis2'],
				'premilebihbasis1'=> $bar['premilebihbasis1'],
				'premilebihbasis2'=> $bar['premilebihbasis2'],
				'premibrondolan'  => $bar['premibrondolan'],
				'upahperhk'       => $bar['upahperhk'],
				'createby'        => $_SESSION['standard']['userid'],
				'createdate'      => date('Y-m-d H:i:s'),
				'updateby'        => $_SESSION['standard']['userid']
            );
            
            $cols = array();
            foreach($data as $key=>$row) {
                    $cols[] = $key;
            }

            # Insert kebun_5basispanen3
            $query = insertQuery($dbname,'kebun_5basispanen3',$data,$cols);
            try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
        }
        
    break;

    case 'ambiltt':

        $str = "select distinct tahuntanam from ".$dbname.".setup_blok where kodeorg like '".$kodeorg."%' order by tahuntanam desc ";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $hasil = $bar['tahuntanam'];
        while ($bar = $res->fetch()) {
            if ($hasil === $bar['tahuntanam']) {
                $opttahuntnm.="<option value=" . $bar['tahuntanam'] . " selected>" . $bar['tahuntanam'] . "</option>";
            }
            $opttahuntnm.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
        }

        echo $opttahuntnm;
    break;

    case'update':
		$data = array(
			'basis1'          => $basis1,
			'basis2'          => $basis2,
			'tidakbasis'      => $tidakbasis,
			'premibasis1'     => $siapbasis,
			'premibasis2'     => $siapbasis2,
			'premilebihbasis1'=> $lebihbasis1,
			'premilebihbasis2'=> $lebihbasis2,
			'premibrondolan'  => $brondol,
			'upahperhk'       => $rphk,
			'updateby'        => $_SESSION['standard']['userid']
		);
		
		$where = "kodeorg='".$kodeorg."' and jenispremi='".$jenis."' and periode='".$periode."' and tahuntanam='".$tahuntanam."'";

		$str = updateQuery($dbname,'kebun_5basispanen3',$data,$where);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
    case'insert':
    
        try {
		$owlPDO->beginTransaction();
			$sql = "select * from " . $dbname . ".kebun_5basispanen3 where kodeorg='" . $kodeorg . "' and jenispremi='" . $jenis . "' and periode='" . $periode . "' and tahuntanam='" . $tahuntanam . "'";
			$jlhbrs = count(fetchdata($sql));
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah ada.");
			}
			
			$data = array(
				'kodeorg'         => $kodeorg,
				'periode'         => $periode,
				'jenispremi'      => $jenis,
				'tahuntanam'      => $tahuntanam,
				'basis1'          => $basis1,
				'basis2'          => $basis2,
				'tidakbasis'      => $tidakbasis,
				'premibasis1'     => $siapbasis,
				'premibasis2'     => $siapbasis2,
				'premilebihbasis1'=> $lebihbasis1,
				'premilebihbasis2'=> $lebihbasis2,
				'premibrondolan'  => $brondol,
				'upahperhk'       => $rphk,
				'createby'        => $_SESSION['standard']['userid'],
				'createdate'      => date('Y-m-d H:i:s'),
				'updateby'        => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}

			# Insert kebun_5basispanen3
			$query = insertQuery($dbname,'kebun_5basispanen3',$data,$cols);
			$owlPDO->exec($query);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
    
    case'deletedetail':
        $str = "delete from " . $dbname . ".kebun_5basispanen3 where kodeorg='" . $kodeorg . "' and jenispremi='" . $jenis . "' and periode='" .$periode. "' and tahuntanam='" . $tahuntanam . "' ";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        break;
    
	case'loaddatadetail':
        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead> <tr class=rowheader>
                    <th align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</th>
                    <th align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['periode'] . "<br>(Berlaku)</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['jenispremi'] . "</th>
					<th align=center rowspan=2 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
					<th align=center colspan=2 width=50px>" . $_SESSION['lang']['basic'] . " (Jjg)</th>
					<th align=center rowspan=2 width=50px>Tidak Basis</th>
					<th align=center colspan=2>Premi Siap Basis (Rp)</th>
					<th align=center colspan=2>" . $_SESSION['lang']['lebihbasis'] . " (Rp)</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "<br>Rp/Kg</th>
					<th align=center rowspan=2>Rp / HK</th>
					<th align=center rowspan=2>" . $_SESSION['lang']['updateby'] . "</th>
					<th align=center rowspan=2 colspan=2>" . $_SESSION['lang']['action'] . "</th>
				</tr>
				 <tr class=rowheader>
                    <th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>
					<th align=center width=60px>I</th>
                    <th align=center width=60px>II</th>
					<th align=center width=50px>I</th>
                    <th align=center width=50px>II</th>  
				</tr></thead>";
        $no = 0;
		$where = "";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and kodeorg in (select kodeorganisasi ".$dbname.".organisasi induk='".$_SESSION['empl']['kodeorganisasi']."')";
		} else {
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		
        $str = "SELECT * FROM " . $dbname . ".kebun_5basispanen3 where 1=1 " . $where . "  order by lastupdate desc ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
            $tab.="<tr class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
            $tab.="<td align=center>".$bar['periode']."</td>";
            $tab.="<td>".$bar['jenispremi']."</td>";
            $tab.="<td align=center>".$bar['tahuntanam']."</td>";
            $tab.="<td align=right>".$bar['basis1']."</td>";
            $tab.="<td align=right>".$bar['basis2']."</td>";
            $tab.="<td align=right>".$bar['tidakbasis']."</td>";
            $tab.="<td align=right>".$bar['premibasis1']."</td>";
            $tab.="<td align=right>".$bar['premibasis2']."</td>";
            $tab.="<td align=right>".$bar['premilebihbasis1']."</td>";
            $tab.="<td align=right>".$bar['premilebihbasis2']."</td>";
            $tab.="<td align=right>".$bar['premibrondolan']."</td>";
            $tab.="<td align=right>".$bar['upahperhk']."</td>";
            $tab.="<td>" . getNamaKaryawan($bar['updateby']) . "</td>";
            
			$isi.="<td align=center width=30px><img src=images/application/application_edit.png class=resicon  title='Edit' 
				onclick=\"editdetail('".$bar['kodeorg']."','".$bar['periode']."','".$bar['jenispremi']."','".$bar['tahuntanam']."','".$bar['basis1']."','".$bar['basis2']."','".$bar['premibasis1']."','".$bar['premilebihbasis1']."','".$bar['premilebihbasis2']."','".$bar['premibrondolan']."','".$bar['tidakbasis']."','".$bar['premibasis2']."','".$bar['upahperhk']."');\" ></td>";
				
			$isi.="<td align=center width=30px><img src=images/application/application_delete.png class=resicon  title='Delete' 
				onclick=\"deldetail('".$bar['kodeorg']."','".$bar['periode']."','".$bar['jenispremi']."','".$bar['tahuntanam']."', 'detail');\" ></td>";
		  
            $tab.=$isi;
        }
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
        break;
    case'loaddata':
        $where = "";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and kodeorg in (select kodeorganisasi ".$dbname.".organisasi induk='".$_SESSION['empl']['kodeorganisasi']."')";
		} else {
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
        
        if ($periode != '') {
            $where.=" and periode='" . $periode . "' ";
        }
        if ($jenis != '') {
            $where.=" and jenispremi='" . $jenis . "' ";
        }
		if ($kodeorg != '') {
            $where.=" and kodeorg='" . $kodeorg . "' ";
        }
		
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
        $sql = "select * from " . $dbname . ".kebun_5basispanen3 where 1=1 " . $where . "";
        $jlhbrs = count(fetchdata($sql));
        
        $tab = "";
		$no = 0;
        $no = $maxdisplay;
		
        $str = "SELECT * FROM " . $dbname . ".kebun_5basispanen3
		where 1=1 " . $where . "  order by periode desc, tahuntanam,jenispremi asc limit " . $offset . "," . $limit . "";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;
            $tab.="<tr class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
            $tab.="<td>".$bar['periode']."</td>";
            $tab.="<td>".$bar['jenispremi']."</td>";
            $tab.="<td align=center>".$bar['tahuntanam']."</td>";
            $tab.="<td align=right>".$bar['basis1']."</td>";
            $tab.="<td align=right>".$bar['basis2']."</td>";
            $tab.="<td align=right>".$bar['tidakbasis']."</td>";
            $tab.="<td align=right>".$bar['premibasis1']."</td>";
            $tab.="<td align=right>".$bar['premibasis2']."</td>";
            $tab.="<td align=right>".$bar['premilebihbasis1']."</td>";
            $tab.="<td align=right>".$bar['premilebihbasis2']."</td>";
            $tab.="<td align=right>".$bar['premibrondolan']."</td>";         
            $tab.="<td align=right>".$bar['upahperhk']."</td>";         
            $tab.="<td>" . getNamaKaryawan($bar['updateby']) . "</td>";
            // $tab.="<td>" . @$nmkar[$bar['updateby']] . "</td>";
            
			$isi.="<td align=center width=30px><img src=images/application/application_edit.png class=resicon  title='Edit' 
				onclick=\"edit('".$bar['kodeorg']."','".$bar['periode']."','".$bar['jenispremi']."','".$bar['tahuntanam']."','".$bar['basis1']."','".$bar['basis2']."','".$bar['premibasis1']."','".$bar['premilebihbasis1']."','".$bar['premilebihbasis2']."','".$bar['premibrondolan']."','".$bar['tidakbasis']."','".$bar['premibasis2']."','".$bar['upahperhk']."');\" ></td>";
				
			$isi.="<td align=center width=30px><img src=images/application/application_delete.png class=resicon  title='Delete' 
				onclick=\"deldetail('".$bar['kodeorg']."','".$bar['periode']."','".$bar['jenispremi']."','".$bar['tahuntanam']."', 'depan');\" ></td>";
            $tab.=$isi;
            $tab.="</tr>";
        }
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
        $footd.="</tr>
                     <tr><td colspan=17 align=center>";
        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }
        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";
        echo $tab . "####" . $footd;
        break;
}
?>	