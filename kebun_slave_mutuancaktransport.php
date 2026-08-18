<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$tgl = tanggalsystem(checkPostGet('tgl', ''));
$method = checkPostGet('method', '');
$divisi = checkPostGet('divisi', '');
$blok = checkPostGet('blok', '');
$kemandoran = checkPostGet('kemandoran', '');
$pemanen = checkPostGet('pemanen', '');
$sph = checkPostGet('sph', '');
$pokoksample = checkPostGet('pokoksample', '');
$pokokpanen = checkPostGet('pokokpanen', '');
$jjgpanen = checkPostGet('jjgpanen', '');
$jlhtph = checkPostGet('jlhtph', '');
$tipe = checkPostGet('tipe','');
$kriteria = checkPostGet('kriteria','');

$divsch = checkPostGet('divsch', '');
$tglsch = tanggalsystem(checkPostGet('tglsch', ''));
$bloksch = checkPostGet('bloksch', '');

$unitexp = checkPostGet('unitexp', '');
$perexp = checkPostGet('perexp', '');

$optmandorpemanen = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nmafd = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$arrdeckriteria = json_decode($kriteria);
$jab = getPostingJabatan('mutu_ancak');

switch ($method) {
    case'getkemandoran':
        $smandor="select karyawanid, namakaryawan, nik from ".$dbname.".datakaryawan where lokasitugas='".substr($divisi,0,4)."' order by namakaryawan";
        $qmandor=$owlPDO->query($smandor) or die(print " Gagal: ".PDOException::getMessage());
        $qmandor->setFetchMode(PDO::FETCH_ASSOC);
        while($rmandor=$qmandor->fetch()){
            $optkemandoran.="<option value=" . $rmandor['karyawanid'] . ">" . $rmandor['namakaryawan'] . " [".$rmandor['nik']."]</option>";
        }

        echo $optkemandoran;
    break;

	case'detail':
		##Get Kriteria Mutu
		$arrKriteria = array();
		$str = "select idjenis, jenis, kriteria, satuan from ".$dbname.".kebun_5jenismutu where jenis!='Mutu Buah' order by jenis asc, idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrKriteria[$bar['idjenis']]['kriteria'] = $bar['kriteria'];
			$arrKriteria[$bar['idjenis']]['satuan'] = $bar['satuan'];
			$arrKriteria[$bar['idjenis']]['jenis'] = $bar['jenis'];
			$arrJenis[$bar['jenis']] = $bar['jenis'];
			$countKriteria[$bar['jenis']] += 1;
			$arrKriteria2[$bar['idjenis']] = $bar['idjenis'];
		}
	
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_rekappnn where divisi='" . $divisi . "' and tanggal='" . $tgl . "' and posting=1";

        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Data untuk divisi : " . $divisi . " ditanggal " . tanggalnormal($tgl) . " sudah di posting");
        }
        OPEN_BOX();
        
		#GET KEMANDORAN
        $optpemanen=$optmandor=$optblok="<option value=''></option>";
        $smandor="select distinct(b.karyawanid) as mandorid, b.namakaryawan, b.nik from ".$dbname.".datakaryawan b 
				where b.lokasitugas='".substr($divisi,0,4)."' and kodejabatan='111' order by namakaryawan";
        $qmandor=$owlPDO->query($smandor) or die(print " Gagal: ".PDOException::getMessage());
        $qmandor->setFetchMode(PDO::FETCH_ASSOC);
        while($rmandor=$qmandor->fetch()){
            $optmandor.="<option value=" . $rmandor['mandorid'] . ">" . $rmandor['namakaryawan'] . " [".$rmandor['nik']."]</option>";
        }

        $sblok = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $divisi . "%'";
        $qblok = $owlPDO->query($sblok) or die(print " Gagal: " . PDOException::getMessage());
        $qblok->setFetchMode(PDO::FETCH_ASSOC);
        while ($rblok = $qblok->fetch()) {
            $optblok.="<option value=" . $rblok['kodeorg'] . ">" . $rblok['kodeorg'] . "</option>";
        }
		
		echo "<input type='hidden' id=kriteria value='".json_encode($arrKriteria2)."'>";
        echo"
        <fieldset >
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>
        <thead>
        <tr class=rowheader>    
            <td align=center rowspan='2'>".$_SESSION['lang']['kemandoran']."</td>
            <td align=center rowspan='2'>".$_SESSION['lang']['pemanen']."</td>
            <td align=center rowspan='2'>".$_SESSION['lang']['blok']."</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['sph'] . "</td>
			<td align=center rowspan='2'>" . $_SESSION['lang']['pokok'] . "<br>".$_SESSION['lang']['sample']."</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['pokok'] . " Yg <br> di ".$_SESSION['lang']['panen']."</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>".$_SESSION['lang']['panen']."</td>";
            // <td align=center rowspan='2'>Jumlah TPH</td>";
		$tempjenis = "";
		if(isset($arrJenis))
		foreach($arrJenis as $key)
		{
			if($tempjenis=='' or $tempjenis==$key)
			{
				echo "<td align=center colspan='".$countKriteria[$key]."'>".$key."</td>";
			}
			else
			{
				echo "<td align=center colspan='".(($countKriteria[$key])+1)."'>".$key."</td>";
			}
			$tempjenis = $key;
		}
        echo"<td align=center rowspan='2'>" . $_SESSION['lang']['action'] . "</td>
        </tr>
        <tr>";
		$tempjenis = "";
		if(isset($arrKriteria))
		foreach($arrKriteria as $key=>$val)
		{
			if($tempjenis=='' or $tempjenis==$val['jenis'])
			{
				echo "<td align=center>".$val['kriteria']."<br>(".$val['satuan'].")</td>";
			}
			else
			{
				echo "<td align=center>Jumlah<br>TPH</td>";
				echo "<td align=center>".$val['kriteria']."<br>(".$val['satuan'].")</td>";
			}
			$tempjenis = $val['jenis'];
		}
        echo"</tr>
        </thead>
        <tr class=rowcontent>   
            <td><select id=kemandoran style=\"width:150px;\" onchange='getpemanen()'>".$optmandor."</select>
				<img id='kemandoran' onclick=z.elSearch('kemandoran',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
            <td><select id=pemanen style='width:150px;'></select>
				<img id='pemanen' onclick=z.elSearch('pemanen',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
            <td style='text-align:center'><select id=blok style=\"width:100px;\" onchange='getsph()'>".$optblok."</select>
				<img id='blok' onclick=z.elSearch('blok',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
            <td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=sph disabled  style=\"width:50px;\"></td>
            <td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=pokoksample style=\"width:50px;\"></td>
            <td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=pokokpanen style=\"width:50px;\"></td>
            <td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=jjgpanen style=\"width:50px;\"></td>";
		$tempjenis = "";
		if(isset($arrKriteria))
		foreach($arrKriteria as $key=>$val)
		{
			if($tempjenis=='' or $tempjenis==$val['jenis'])
			{
				echo "<td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=kriteria_".$key." style=\"width:50px;\"></td>";
			}
			else
			{
				echo "<td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=jlhtph style=\"width:50px;\"></td>";
				echo "<td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  id=kriteria_".$key." style=\"width:50px;\"></td>";
			}
			$tempjenis = $val['jenis'];
		}
        echo"<td style='text-align:center'><input type=hidden id=method value='insert'>
                <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=\"savedetail()\" src='images/save.png'/>
            </td>
        </tr>
        </table>
        <button id=done class=mybutton onclick=cancel()>" . $_SESSION['lang']['selesai'] . "</button>
        </fieldset>";
        // style='float:left;'
        echo"
        <fieldset ><legend>" . $_SESSION['lang']['list'] . " " . $_SESSION['lang']['detail'] . "</legend>
        <div id=loaddatadetail>
            <script>loaddatadetail()</script>
         </fieldset>";

        CLOSE_BOX();
    break;

    case'loaddatadetail':
		##Get Kriteria Mutu
		$arrKriteria = array();
		$str = "select idjenis, jenis, kriteria, satuan from ".$dbname.".kebun_5jenismutu where jenis!='Mutu Buah' order by jenis asc, idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrKriteria[$bar['idjenis']]['kriteria'] = $bar['kriteria'];
			$arrKriteria[$bar['idjenis']]['satuan'] = $bar['satuan'];
			$arrKriteria[$bar['idjenis']]['jenis'] = $bar['jenis'];
			$arrJenis[$bar['jenis']] = $bar['jenis'];
			$countKriteria[$bar['jenis']] += 1;
			$arrKriteria2[$bar['idjenis']] = $bar['idjenis'];
		}
        echo"
        <table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>
        <thead>
        <tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>    
            <td align=center rowspan='2' style=width:150px>".$_SESSION['lang']['kemandoran']."</td>
            <td align=center rowspan='2' style=width:150px>".$_SESSION['lang']['pemanen']."</td>
            <td align=center rowspan='2' style=width:80px>".$_SESSION['lang']['blok']."</td>
            <td align=center rowspan='2' style=\"width:50px;\">" . $_SESSION['lang']['sph'] . "</td>
            <td align=center rowspan='2' style=\"width:50px;\">" . $_SESSION['lang']['pokok'] . "<br>".$_SESSION['lang']['sample']."</td>
            <td align=center rowspan='2' style=\"width:50px;\">" . $_SESSION['lang']['pokok'] . " Yg <br> di ".$_SESSION['lang']['panen']."</td>
            <td align=center rowspan='2' style=\"width:50px;\">" . $_SESSION['lang']['jjg'] . "<br>".$_SESSION['lang']['panen']."</td>";
		$tempjenis = "";
		if(isset($arrJenis))
		foreach($arrJenis as $key)
		{
			if($tempjenis=='' or $tempjenis == $key)
			{
				echo "<td align=center colspan='".$countKriteria[$key]."'>".$key."</td>";
			}
			else
			{
				echo "<td align=center colspan='".(($countKriteria[$key])+1)."'>".$key."</td>";
			}
			$tempjenis = $key;
		}
        echo"<td align=center rowspan='2'>" . $_SESSION['lang']['action'] . "</td>
        </tr>
        <tr>";
		$tempjenis = "";
		if(isset($arrKriteria))
		foreach($arrKriteria as $key=>$val)
		{
			if($tempjenis=='' or $tempjenis==$val['jenis'])
			{
				echo "<td align=center>".$val['kriteria']."<br>(".$val['satuan'].")</td>";
			}
			else
			{
				echo "<td align=center>Jumlah TPH</td>";
				echo "<td align=center>".$val['kriteria']."<br>(".$val['satuan'].")</td>";
			}
			$tempjenis = $val['jenis'];
		}
		echo"</tr>
        </thead>";
		
		##Get Detail Kriteria
		$arrnilaikriteria = array();
		$str = "select * from ".$dbname.".kebun_mutuancaktransportdt where blok like '".$divisi."%' and tanggal = '".$tgl."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrnilaikriteria[$bar['pemanen']][$bar['blok']][$bar['jenismutu']] += $bar['nilai'];
		}
        
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_mutuancaktransportht where divisi like '" . $divisi . "%' and tanggal='" . $tgl . "' order by tanggal desc, divisi asc, kemandoran asc ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
        echo"<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=left>" . $optmandorpemanen[$bar['kemandoran']] . "</td>
                <td align=left>" . $optmandorpemanen[$bar['pemanen']] . "</td>
                <td align=left>" . $bar['blok'] . "</td>
                <td align=right>" . @number_format($bar['sph'], 2) . "</td>
                <td align=right>" . @number_format($bar['pokoksample'], 2) . "</td>
                <td align=right>" . @number_format($bar['pokokpanen'], 2) . "</td>
                <td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
		$tempjenis = '';
        if(isset($arrKriteria))
		foreach($arrKriteria as $key=>$val)
		{
			$jjg = $arrnilaikriteria[$bar['pemanen']][$bar['blok']][$key];
			if($tempjenis=='' or $tempjenis==$val['jenis'])
			{
				echo"<td align=right>".@number_format($jjg, 2)."</td>";
			}
			else
			{
				echo"<td align=right>".@number_format($bar['tph'])."</td>";
				echo"<td align=right>".@number_format($jjg, 2)."</td>";
			}
			$tempjenis = $val['jenis'];
			$arrTotal[$key] += $jjg;
		}
		echo"<td align=center>
                <img src=images/application/application_delete.png class=resicon  title='Delete' 
                onclick=\"deletedetail('".$bar['kemandoran']."','".tanggalnormal($bar['tanggal'])."','".$bar['blok']."','".$bar['pemanen']."','".$bar['divisi']."');\" ></td>";
                @$tsph+=$bar['sph'];
                @$tpokoksample+=$bar['pokoksample'];
                @$tpokokpanen+=$bar['pokokpanen'];
                @$tjjgpanen+=$bar['jjgpanen'];
                @$tjlhtph+=$bar['tph'];
        }
        echo"<tr class=rowcontent>
                <td align=right colspan=4><b>" . $_SESSION['lang']['total'] . "</td>
                <td align=right><b>" . @number_format($tsph, 2) . "</td>
                <td align=right><b>" . @number_format($tpokoksample, 2) . "</td>
                <td align=right><b>" . @number_format($tpokokpanen, 2) . "</td>
                <td align=right><b>" . @number_format($tjjgpanen) . "</td>";
		$tempjenis = "";
		if(isset($arrKriteria))
		foreach($arrKriteria as $key=>$val)
		{
			if($tempjenis=='' or $tempjenis==$val['jenis'])
			{
				echo"<td align=right><b>" . @number_format($arrTotal[$key], 2) . "</td>";
			}
			else
			{
				echo "<td align=right><b>" . @number_format($tjlhtph) . "</td>";
				echo"<td align=right><b>" . @number_format($arrTotal[$key], 2) . "</td>";
			}
			$tempjenis = $val['jenis'];
		}		
		echo"<td ></td>
                </tr>
        </table>";
    break;

    case'getsph':
        $sql = "select * from " . $dbname . ".setup_blok where kodeorg = '" . $blok . "' ";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $sph = @number_format($bar['jumlahpokok']/$bar['luasareaproduktif'],2);

        echo $sph;
    break;
	
	case'getpemanen':
		$opt = "";
        $str = "select b.karyawanid, b.namakaryawan, b.nik from ".$dbname.".datakaryawan b
			where  b.lokasitugas='".substr($_SESSION['empl']['lokasitugas'],0,4)."' and kodejabatan!='111'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch())
		{
			$opt .= "<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."[".$bar['nik']."]</option>";
		}
		
        echo $opt;
    break;

    case'insert':
		#cek data
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_mutuancaktransportdt where kemandoran='".$kemandoran."' and pemanen='".$pemanen."' and blok='".$blok."' and tanggal='".$tgl."'";

        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) 
		{
			$optMandor = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$kemandoran."'");
			$optPanen = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$pemanen."'");
            exit("Warning : Data untuk :\nTanggal : ".tanggalnormal($tgl)."\nKemandoran : ".$optMandor[$kemandoran]."\nPemanen : ".$optPanen[$pemanen]."\nBlok : ".$blok."\nsudah ada.");
        }

        $str = "insert into " . $dbname . ".kebun_mutuancaktransportht (divisi,tanggal,kemandoran,pemanen,blok,sph,pokoksample,pokokpanen,jjgpanen,tph,updateby) values ('".$divisi."','".$tgl. "','".$kemandoran."','".$pemanen."','".$blok."','".$sph."','".$pokoksample."','".$pokokpanen."','".$jjgpanen."','".$jlhtph."','".$_SESSION['standard']['userid']."')";
		try 
		{
			$owlPDO->exec($str);
			if(isset($arrdeckriteria))
			foreach($arrdeckriteria as $key)
			{
				$nilai = checkPostGet("kriteria_".$key,"0");
				$str2 = "insert into ".$dbname.".kebun_mutuancaktransportdt (tanggal,kemandoran,pemanen,blok,jenismutu,nilai) value ('".$tgl."','".$kemandoran."','".$pemanen."','".$blok."','".$key."','".$nilai."')";
				
				try 
				{
					$owlPDO->exec($str2);
				}
				catch (PDOException $e) 
				{
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
		} 
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
    break;

    case'deletedetail':

        $str = "delete from " . $dbname . ".kebun_mutuancaktransportht where kemandoran='".$kemandoran."' and tanggal='".$tgl."' and blok='".$blok."'";
        try 
		{
            $owlPDO->exec($str);
			$str2 = "delete from ".$dbname.".kebun_mutuancaktransportdt where kemandoran='".$kemandoran."' and tanggal='".$tgl."' and blok='".$blok."'";
			try 
			{
				$owlPDO->exec($str2);
			}
			catch (PDOException $e) 
			{
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
        } 
		catch (PDOException $e) 
		{
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case'loaddata':
        $where = "";
        $where.=" and divisi like '" . $_SESSION['empl']['lokasitugas'] . "%' ";
        if ($divsch != '') {
            $where.=" and divisi='" . $divsch . "' ";
            $where2.=" and blok='".$divsch."%'";
        }
        if ($tglsch != '') {
            $where.=" and tanggal='" . $tglsch . "' ";
            $where2.=" and tanggal='" . $tglsch . "' ";
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
		
		##Get Kriteria Mutu
		$arrKriteria = array();
		$str = "select idjenis, jenis, kriteria, satuan from ".$dbname.".kebun_5jenismutu where jenis!='Mutu Buah' order by jenis asc, idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrKriteria[$bar['idjenis']]['kriteria'] = $bar['kriteria'];
			$arrKriteria[$bar['idjenis']]['satuan'] = $bar['satuan'];
			$arrKriteria[$bar['idjenis']]['jenis'] = $bar['jenis'];
			$arrJenis[$bar['jenis']] = $bar['jenis'];
			$countKriteria[$bar['jenis']] += 1;
			$arrKriteria2[$bar['idjenis']] = $bar['idjenis'];
		}
		
		##Get Detail Kriteria
		$arrnilaikriteria = array();
		$str = "select * from ".$dbname.".kebun_mutuancaktransportdt where 1=1 ".$where2."";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrnilaikriteria[$bar['tanggal']][substr($bar['blok'],0,6)][$bar['kemandoran']][$bar['jenismutu']] += $bar['nilai'];
		}
		
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_mutuancaktransportht where 1=1 " . $where . " group by divisi,tanggal,kemandoran";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;

        $str = "SELECT *,sum(sph) as sph,sum(pokoksample) as pokoksample,"
                . "sum(pokokpanen) as pokokpanen,sum(jjgpanen) as jjgpanen, sum(tph) as tph FROM " . $dbname . ".kebun_mutuancaktransportht
        where 1=1 " . $where . " group by divisi,tanggal,kemandoran order by tanggal desc,divisi asc limit " . $offset . "," . $limit . "";
        $tab = "";
        $no = $maxdisplay;

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $isi = '';
            $no+=1;

            $tab.="<tr class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td >" . tanggalnormal($bar['tanggal']) . "</td>";
            $tab.="<td>" . $bar['divisi'] . "</td>";
            $tab.="<td>" . $optmandorpemanen[$bar['kemandoran']] . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['pokoksample']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['pokokpanen']) . "</td>";
            $tab.="<td  align=right>" . @number_format($bar['jjgpanen']) . "</td>";
			$tempjenis = "";
			if(isset($arrKriteria))
			foreach($arrKriteria as $key=>$val)
			{
				if($tempjenis=='' or $tempjenis==$val['jenis'])
				{
					$tab.="<td  align=right>".@number_format($arrnilaikriteria[$bar['tanggal']][$bar['divisi']][$bar['kemandoran']][$key], 2)."</td>";
				}
				else
				{
					$tab.="<td  align=right>".@number_format($bar['tph'])."</td>";
					$tab.="<td  align=right>".@number_format($arrnilaikriteria[$bar['tanggal']][$bar['divisi']][$bar['kemandoran']][$key], 2)."</td>";
				}
				$tempjenis = $val['jenis'];
			}
            if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"edit('" .$bar['divisi'] . "','" . $bar['kemandoran'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('".$bar['divisi']."','".tanggalnormal($bar['tanggal'])."','".$bar['kemandoran']."');\" ></td>";
               
               $isi.="<td align=center><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' 
                    onclick=\"posting('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $no . "');\" ></td>";
            } else {
                if(in_array($_SESSION['empl']['jabatan'],$jab)){
                    $icon="images/icons/04/16/04.png";
                    $title="Unposting";
                    $unpost=" onclick=\"unposting('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $no . "');\" ";
                }else {
                    $icon="images/icons/04/16/02.png";
                    $title="Posted";
                    $unpost='';
                }
                $isi.="<td align=center style='width:10px'></td><td align=center style='width:10px'></td>";
                $isi.="<td align=center><img src=".$icon." class=resicon class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $isi.="<td align=right><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"htmldt('".$bar['divisi']."','".tanggalnormal($bar['tanggal'])."','".$bar['kemandoran']."','html',event);\" ></td>";

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
                     <tr><td colspan=16 align=center>";

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

    case'html':

        $theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }  

        echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>"; 

        if($tipe=='excel'){
            $border="border=1";
        }else{
			$border="border=0";
			echo" Print Excel : <img style=cursor:pointer; "
			. " onclick=\"parent.htmldt('".$divisi."','".tanggalnormal($tgl)."','".$kemandoran."','excel',event)\" src=images/excel.jpg	title='MS.Excel'>";
        }

        ##Get Kriteria Mutu
		$arrKriteria = array();
		$str = "select idjenis, jenis, kriteria, satuan, satuan2 from ".$dbname.".kebun_5jenismutu where jenis!='Mutu Buah' order by jenis asc, idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrKriteria[$bar['idjenis']]['kriteria'] = $bar['kriteria'];
			$arrKriteria[$bar['idjenis']]['satuan'] = $bar['satuan'];
			$arrKriteria[$bar['idjenis']]['satuan2'] = $bar['satuan2'];
			$arrKriteria[$bar['idjenis']]['jenis'] = $bar['jenis'];
			$arrJenis[$bar['jenis']] = $bar['jenis'];
			$arrJenis2[$bar['idjenis']] = $bar['jenis'];
			$countKriteria[$bar['jenis']] += 1;
			$arrKriteria2[$bar['idjenis']] = $bar['idjenis'];
		}
		
        $tab = "<table $border cellpadding=1 cellspacing=1 class=sortable width=100%>
                <thead>
                <tr class=rowheader>
                    <td align=center rowspan='3'>" . $_SESSION['lang']['nourut'] . "</td>    
                    <td align=center rowspan='3' >".$_SESSION['lang']['pemanen']."</td>
                    <td align=center rowspan='3' >".$_SESSION['lang']['blok']."</td>
                    <td align=center rowspan='3' >" . $_SESSION['lang']['sph'] . "</td>
                    <td align=center rowspan='3' >" . $_SESSION['lang']['pokok'] . "<br>".$_SESSION['lang']['sample']."</td>
                    <td align=center rowspan='3' >" . $_SESSION['lang']['pokok'] . " Yg <br> di ".$_SESSION['lang']['panen']."</td>
                    <td align=center rowspan='3' >" . $_SESSION['lang']['jjg'] . "<br>".$_SESSION['lang']['panen']."</td>";
				$tempjenis = '';
                if(isset($arrJenis))
				foreach($arrJenis as $key)
				{
					if($tempjenis=='' or $tempjenis==$key)
					{
						$tab .= "<td align=center colspan='".(($countKriteria[$key]*2))."'>".$key."</td>";
					}
					else
					{
						$tab .= "<td align=center colspan='".(($countKriteria[$key]*2)+1)."'>".$key."</td>";
					}
					$tempjenis=$key;
				}
				$tab.="<td align=center rowspan='3'>AKP</td>
                </tr>
                <tr>";
				$tempjenis='';
				if(isset($arrKriteria))
				foreach($arrKriteria as $key=>$val)
				{
					if($tempjenis=='' or $tempjenis==$val['jenis'])
					{
						$tab.="<td align=center colspan='2'>".$val['kriteria']."</td>";
					}
					else
					{
						$tab.="<td align=center rowspan='2' >Jumlah<br>TPH</td>";
						$tab.="<td align=center colspan='2'>".$val['kriteria']."</td>";
					}
					$tempjenis=$val['jenis'];
				}    
                $tab.="</tr>
                <tr>";
				
				$tempjenis='';
				if(isset($arrKriteria))
				foreach($arrKriteria as $key=>$val)
				{
					$tab.="<td align=center>".$val['satuan']."</td>";
					$tab.="<td align=center>".$val['satuan2']."</td>";
				}      
                $tab.="</tr>
                </thead>";
				
				##Get Detail Kriteria
				$arrnilaikriteria = array();
				$str = "select * from ".$dbname.".kebun_mutuancaktransportdt where blok like '".$divisi."%' and tanggal = '".$tgl."'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) 
				{
					$arrnilaikriteria[$bar['pemanen']][$bar['blok']][$bar['jenismutu']] += $bar['nilai'];
				}
				
                $no = 0;
                $str = "select * from " . $dbname . ".kebun_mutuancaktransportht where divisi like '" . $divisi . "%' and tanggal='" . $tgl . "' ";

                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>" . $no . "</td>
                           <td align=left>" . $optmandorpemanen[$bar['pemanen']] . "</td>
                           <td align=left>" . $bar['blok'] . "</td>
                           <td align=right>" . @number_format($bar['sph'], 2) . "</td>
                           <td align=right>" . @number_format($bar['pokoksample'], 2) . "</td>
                           <td align=right>" . @number_format($bar['pokokpanen'], 2) . "</td>
                           <td align=right>" . @number_format($bar['jjgpanen'],2) . "</td>";
						   
					$tempjenis = '';
					if(isset($arrKriteria))
					foreach($arrKriteria as $key=>$val)
					{
						$jjg = $arrnilaikriteria[$bar['pemanen']][$bar['blok']][$key];
						if($arrJenis2[$key] == 'Mutu Hancak')
						{
							if($val['kriteria'] == 'Brondolan Tinggal')
							{
								@$sjjg = $jjg / $bar['pokokpanen'];
							}
							else
							{
								@$sjjg = $jjg / ($bar['pokoksample']/$bar['sph']);
							}
						}
						else
						{
							@$sjjg = $jjg / $bar['tph'];
						}
						
						if($tempjenis=='' or $tempjenis==$val['jenis'])
						{
							$tab.="<td align=right>".@number_format($jjg, 2)."</td>";
							$tab.="<td align=right>".@number_format($sjjg, 2)."</td>";
						}
						else
						{
							$tab.="<td align=right>".@number_format($bar['tph'],2)."</td>";
							$tab.="<td align=right>".@number_format($jjg, 2)."</td>";
							$tab.="<td align=right>".@number_format($sjjg, 2)."</td>";
						}
						
						$tempjenis = $val['jenis'];
						$arrTotal[$key] += $jjg;
						$arrTotal2[$key] += $sjjg;
					}
					$akp = $bar['jjgpanen'] / $bar['pokoksample'];
					$tab.="<td align=right>".@number_format($akp, 2)."</td>";
                    $mandor=$optmandorpemanen[$bar['kemandoran']];
                    @$tsph+=$bar['sph'];
                    @$tpokoksample+=$bar['pokoksample'];
                    @$tpokokpanen+=$bar['pokokpanen'];
                    @$tjjgpanen+=$bar['jjgpanen'];
                    @$tjlhtph+=$bar['tph'];
                }
            $tab.="</tr>";
            $tab.="<tr class=rowcontent>
                    <td align=center disabled colspan=3><b>" . $_SESSION['lang']['total'] . "</td>
                    <td align=right><b>" . @number_format($tsph, 2) . "</td>
                    <td align=right><b>" . @number_format($tpokoksample	, 2) . "</td>
                    <td align=right><b>" . @number_format($tpokokpanen, 2) . "</td>
                    <td align=right><b>" . @number_format($tjjgpanen,2) . "</td>";
					
			$tempjenis = "";
			if(isset($arrKriteria))
			foreach($arrKriteria as $key=>$val)
			{
				if($arrJenis2[$key] == 'Mutu Hancak')
				{
					if($val['kriteria'] == 'Brondolan Tinggal')
					{
						@$sjjg = $arrTotal[$key] / $tpokokpanen;
					}
					else
					{
						@$sjjg = $arrTotal[$key] / ($tpokoksample/$tsph);
					}
				}
				else
				{
					@$sjjg = $arrTotal[$key] / $tjlhtph;
				}
				
				if($tempjenis=='' or $tempjenis==$val['jenis'])
				{
					$tab.="<td align=right><b>" . @number_format($arrTotal[$key], 2) . "</td>";
					$tab.="<td align=right>".@number_format($sjjg, 2)."</td>";
				}
				else
				{
					$tab.="<td align=right><b>".@number_format($tjlhtph,2)."</td>";
					$tab.="<td align=right><b>".@number_format($arrTotal[$key], 2)."</td>";
					$tab.="<td align=right>".@number_format($sjjg, 2)."</td>";
				}
				
				$tempjenis = $val['jenis'];
			}
            $tab.="<td align=right><b>" . @number_format($tjjgpanen/$tpokoksample,2) . "</td>";
            $tab.="</tr>";
			
			//==========================
            $tab.="<tr class=rowcontent>
				<td align=right rowspan=2><b>Standard</td>
				<td align=left colspan=6><b>Tertinggi</b></td>";
			
			//get $pt
			$pt=substr($divisi,0,2);			
			
			$tempjenis = '';
			if(isset($arrKriteria))
			foreach($arrKriteria as $key=>$val)
			{
				$str = "select min(rangedari) as maksimal from ".$dbname.".kebun_5mutu where idjenis='".$key."'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
				
				if($tempjenis=='' or $tempjenis==$val['jenis'])
				{
					$tab.="<td align=right><b>".@number_format($bar['maksimal'],2)."</b></td>";
				}
				else
				{
					$tab.="<td></td>";
					$tab.="<td align=right><b>".@number_format($bar['maksimal'],2)."</b></td>";
				}
				$tab.="<td></td>";
				
				$tempjenis = $val['jenis'];
			}
			$tab.="<td></td>
			</tr>";
			
			$tab.="<tr class=rowcontent>
				<td align=left colspan=6><b>Terendah</b></td>";
			
			//get $pt
			$pt=substr($divisi,0,2);			
			
			$tempjenis = '';
			if(isset($arrKriteria))
			foreach($arrKriteria as $key=>$val)
			{
				$str = "select max(rangedari) as minimal from ".$dbname.".kebun_5mutu where idjenis='".$key."'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                $bar = $res->fetch();
				
				if($tempjenis=='' or $tempjenis==$val['jenis'])
				{
					$tab.="<td align=right><b>".@number_format($bar['minimal'],2)."</b></td>";
				}
				else
				{
					$tab.="<td></td>";
					$tab.="<td align=right><b>".@number_format($bar['minimal'],2)."</b></td>";
				}
				$tab.="<td></td>";
				$tempjenis=$val['jenis'];
			}
			$tab.="<td></td>
			</tr>
			</tr>
		</table>";
	
	
    if ($tipe=='excel')
	{
		$stream="<br>Tanggal : ".tanggalnormal($tgl)."<br>
				 Divisi : ".$divisi."<br>
                 Mandor : ".$mandor."<br>";
        $stream.=$tab;
        $nop_ = "Laporan_Mutu_Ancak_Transport_Divisi_" . $divisi."_". date('Ymd_His');
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                            parent.window.alert('Cant convert to excel format');
                            </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
            }
            fclose($handle);
        }
    }else
    {
   echo $tab;
    } 
    break;

    case'posting':

        $str = "update " . $dbname . ".kebun_mutuancaktransportht set posting='1',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where divisi like '" . $divisi . "%' and tanggal='" . $tgl . "' ";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

     case'delete':

        $str = "delete from " . $dbname . ".kebun_mutuancaktransportht where divisi='" . $divisi . "' and tanggal='" . $tgl . "' and kemandoran='".$kemandoran."'";
        try 
		{
			$owlPDO->exec($str);
			$str2 = "delete from ".$dbname.".kebun_mutuancaktransportdt where blok like '".$divisi."%' and tanggal='".$tgl."' and kemandoran = '".$kemandoran."'";
			try 
			{
				$owlPDO->exec($str2);
			} 
			catch (PDOException $e) 
			{
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
        } 
		catch (PDOException $e)
		{
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case'unposting':
        
        $str = "update " . $dbname . ".kebun_mutuancaktransportht set posting='0',postingdate='" . date('Y-m-d') . "',"
                . "postingby='" . $_SESSION['standard']['userid'] . "' where divisi like '" . $divisi . "%' and tanggal='" . $tgl . "' ";

        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case'excel':

        $tab = "<table cellpadding=1 cellspacing=1 border=1 class=sortable  style=width:100%>
                <thead>
                <tr class=rowheader>
                    <td align=center rowspan='3'>" . $_SESSION['lang']['nourut'] . "</td>    
                    <td align=center rowspan='3'>" . $_SESSION['lang']['divisi'] . "</td>
                    <td align=center rowspan='3'>" . $_SESSION['lang']['kemandoran'] . "</td>
                    <td align=center rowspan='3'>".$_SESSION['lang']['pemanen']."</td>
                    <td align=center rowspan='3'>".$_SESSION['lang']['blok']."</td>
                    <td align=center rowspan='3'>" . $_SESSION['lang']['sph'] . "</td>
                    <td align=center rowspan='3'>" . $_SESSION['lang']['pokok'] . " ".$_SESSION['lang']['sample']."</td>
                    <td align=center rowspan='3'>" . $_SESSION['lang']['pokok'] . " Yg di ".$_SESSION['lang']['panen']."</td>
                    <td align=center rowspan='3'>" . $_SESSION['lang']['jjg'] . " ".$_SESSION['lang']['panen']."</td>
                    <td align=center colspan='4'>" . $_SESSION['lang']['mutuancak'] . "</td>
                    <td align=center colspan='5'>" . $_SESSION['lang']['mutu'] . " ".$_SESSION['lang']['transport']."</td>
                    <td align=center rowspan='3'>AKP</td>
                </tr>
                <tr>
                    <td align=center colspan=2>" . $_SESSION['lang']['buah'] . " ".$_SESSION['lang']['tinggal']." (".$_SESSION['lang']['jjg'].")</td>
                    <td align=center colspan=2>Brondolan ".$_SESSION['lang']['tinggal']." (".$_SESSION['lang']['btr'].")</td>
                    <td align=center rowspan=2>".$_SESSION['lang']['jumlah']." TPH</td>
                    <td align=center colspan=2>" . $_SESSION['lang']['buah'] . " ".$_SESSION['lang']['tinggal']." (".$_SESSION['lang']['jjg'].")</td>
                    <td align=center colspan=2>Brondolan ".$_SESSION['lang']['tinggal']." (".$_SESSION['lang']['btr'].")</td>        
                </tr>
                <tr>
                    <td align=center>".$_SESSION['lang']['jjg'] . "</td>
                    <td align=center>".$_SESSION['lang']['jjg']."/".$_SESSION['lang']['ha'].")</td>
                    <td align=center>".$_SESSION['lang']['btr']."</td>
                    <td align=center>".$_SESSION['lang']['btr'] . "/".$_SESSION['lang']['pokok']."</td>
                    <td align=center>".$_SESSION['lang']['jjg']."</td>        
                    <td align=center>".$_SESSION['lang']['jjg']."/TPH</td>        
                    <td align=center>".$_SESSION['lang']['btr']."</td>        
                    <td align=center>".$_SESSION['lang']['btr']."/TPH</td>        
                </tr>
                </thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_mutuancaktransport where divisi like '" . $unitexp . "%' "
                . " and tanggal like '" . $perexp . "%' order by tanggal asc,blok asc ";
 
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>
            <td align=left >" . $bar['divisi'] . "</td>
                           <td align=left>" . $optmandorpemanen[$bar['kemandoran']] . "</td>
                           <td align=left>" . $optmandorpemanen[$bar['pemanen']] . "</td>
                           <td align=left>" . $bar['blok'] . "</td>
                           <td align=right>" . @number_format($bar['sph'], 2) . "</td>
                           <td align=right>" . @number_format($bar['pokoksample'], 2) . "</td>
                           <td align=right>" . @number_format($bar['pokokpanen'], 2) . "</td>
                           <td align=right>" . @number_format($bar['jjgpanen'],2) . "</td>
                           <td align=right>" . @number_format($bar['jjgancak'], 2) . "</td>
                           <td align=right>" . @number_format(($bar['jjgancak']/$bar['pokoksample']), 2) . "</td>
                           <td align=right>" . @number_format($bar['btrancak'],2) . "</td>
                           <td align=right>" . @number_format(($bar['btrancak']/$bar['pokoksample']),2) . "</td>
                           <td align=right>" . @number_format($bar['jumlahtph'],2) . "</td>
                           <td align=right>" . @number_format($bar['jjgtransport'],2) . "</td>
                           <td align=right>" . @number_format(($bar['jjgtransport']/$bar['jumlahtph']),2) . "</td>
                           <td align=right>" . @number_format($bar['btrtransport'],2) . "</td>
                           <td align=right>" . @number_format(($bar['btrtransport']/$bar['jumlahtph']),2) . "</td>
                           <td align=right>" . @number_format(($bar['jjgpanen']/$bar['pokoksample']),2) . "</td>";
                    @$tsph+=$bar['sph'];
                    @$tpokoksample+=$bar['pokoksample'];
                    @$tpokokpanen+=$bar['pokokpanen'];
                    @$tjjgpanen+=$bar['jjgpanen'];
                    @$tjjgancak+=$bar['jjgancak'];
                    @$tjjgancakha=$tjjgancak/$tpokoksample;
                    @$tbtrancak+=$bar['btrancak'];
                    @$tbtrancakpokok=$tbtrancak/$tpokoksample;
                    @$tjumlahtph+=$bar['jumlahtph'];
                    @$tjjgtransport+=$bar['jjgtransport'];
                    @$tjjgtransporttph=$tjjgtransport/$tjumlahtph;
                    @$tbtrtransport+=$bar['btrtransport'];
                    @$tbtrtransporttph=$tbtrtransport/$tjumlahtph;
                    @$takp=$tjjgpanen/$tpokoksample;
                }
            $tab.="</tr>";
            $tab.="<tr class=rowcontent>
                    <td align=right colspan=5><b>" . $_SESSION['lang']['total'] . "</td>
                    <td align=right><b>" . @number_format($tsph, 2) . "</td>
                    <td align=right><b>" . @number_format($tpokoksample, 2) . "</td>
                    <td align=right><b>" . @number_format($tpokokpanen, 2) . "</td>
                    <td align=right><b>" . @number_format($tjjgpanen) . "</td>
                    <td align=right><b>".@number_format($tjjgancak,2)."</td>
                    <td align=right><b>".@number_format($tjjgancakha,2)."</td>
                    <td align=right><b>" . @number_format($tbtrancak,2) . "</td>
                    <td align=right><b>" . @number_format($tbtrancakpokok,2) . "</td>
                    <td align=right><b>" . @number_format($tjumlahtph,2) . "</td>
                    <td align=right><b>" . @number_format($tjjgtransport,2) . "</td>
                    <td align=right><b>" . @number_format($tjjgtransporttph,2) . "</td>
                    <td align=right><b>" . @number_format($tbtrtransport,2) . "</td>
                    <td align=right><b>" . @number_format($tbtrtransporttph,2) . "</td>
                    <td align=right><b>" . @number_format($takp,2) . "</td>";
        $tab.="</tr>";
        $tab.="</table>";

        $stream = $tab;
        $nop_ = "Laporan_Mutu_Ancak_Transport" . date('Ymd_His');
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                            parent.window.alert('Cant convert to excel format');
                            </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
            }
            closedir($handle);
        }

        break;


}

?>