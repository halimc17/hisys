<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');


$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
    $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }   

$tgl = tanggalsystem(checkPostGet('tgl', ''));
$method = checkPostGet('method', '');
$div = checkPostGet('div', '');
$blok = checkPostGet('blok', '');
$totaljjg = checkPostGet('totaljjg', '');
// $buahmatang = checkPostGet('buahmatang', '');
// $buahmentah = checkPostGet('buahmentah', '');
// $kurangmatang = checkPostGet('kurangmatang', '');
// $lewatmatang = checkPostGet('lewatmatang', '');
// $jjgkosong = checkPostGet('jjgkosong', '');
// $tangkaipanjang = checkPostGet('tangkaipanjang', '');
$jab = getPostingJabatan('mutu_ancak');

$tipe = checkPostGet('tipe','');
$kriteria = checkPostGet('kriteria','');

$divsch = checkPostGet('divsch', '');
$tglsch = tanggalsystem(checkPostGet('tglsch', ''));
$bloksch = checkPostGet('bloksch', '');

$unitexp = checkPostGet('unitexp', '');
$perexp = checkPostGet('perexp', '');

$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

$arrdeckriteria = json_decode($kriteria);

switch ($method) {
	case'detail':
        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_mutubuah where divisi='" . $div . "' and tanggal='" . $tgl . "' and posting=1";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) {
            exit("Error : Data untuk divisi : " . $div . " ditanggal " . tanggalnormal($tgl) . " sudah di posting");
        }

        OPEN_BOX();
        $optblok = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sql = "select * from " . $dbname . ".setup_blok where kodeorg like '" . $div . "%' ";

        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $optblok.="<option value=" . $bar['kodeorg'] . ">" . $bar['kodeorg'] . "</option>";
        }
		
		##Get Kriteria Mutu
		$arrKriteria = array();
		$str = "select idjenis, kriteria from ".$dbname.".kebun_5jenismutu where jenis='Mutu Buah' order by idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) 
		{
            $arrKriteria[$bar['idjenis']] = $bar['kriteria'];
            $arrKriteria2[$bar['idjenis']] = $bar['idjenis'];
        }
		
		echo "<input type='hidden' id=kriteria value='".json_encode($arrKriteria2)."'>";
		
        echo"
        <fieldset >
        <legend>" . $_SESSION['lang']['detail'] . "</legend>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead>
        <tr class=rowheader>    
            <td align=center>".$_SESSION['lang']['blok']."</td>
            <td align=center>" . $_SESSION['lang']['total'] . "<br>".$_SESSION['lang']['jjg']."</td>";
			
			foreach($arrKriteria as $key=>$val)
			{
				echo "<td align=center width=50px>".$val."</td>";
			}
			
        echo"<td align=center>" . $_SESSION['lang']['action'] . "</td>
        </tr>
        </thead>
        <tr class=rowcontent>   
            <td><select id=blok style=\"width:100px;\" >".$optblok."</select></td>
            <td><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\" id=totaljjg style=\"width:50px;\"></td>";
			foreach($arrKriteria as $key=>$val)
			{
				echo "<td style='text-align:center'><input class=myinputtextnumber onkeypress=\"return angka_doang(event);\" id=kriteria_".$key." style=\"width:50px;\"></td>";
			}
			
            echo"<td align=center><input type=hidden id=method value='insert'>
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
		$str = "select idjenis, kriteria from ".$dbname.".kebun_5jenismutu where jenis='Mutu Buah' order by idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) 
		{
            $arrKriteria[$bar['idjenis']] = $bar['kriteria'];
        }
        echo"
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead>
        <tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>    
            <td align=center width=100px>".$_SESSION['lang']['blok']."</td>
            <td align=center width=50px>" . $_SESSION['lang']['total'] . "<br>".$_SESSION['lang']['jjg']."</td>";
		foreach($arrKriteria as $key=>$val)
		{
			echo "<td align=center width=50px>".$val."</td>";
		}
        echo "<td align=center>" . $_SESSION['lang']['action'] . "</td>
        </tr>
        </thead>";
		
		##Get Detail Kriteria
		$arrnilaikriteria = array();
		$str = "select * from ".$dbname.".kebun_mutubuahdt where blok like '".$div."%' and tanggal='".$tgl."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrnilaikriteria[$bar['blok']][$bar['jenismutu']] += $bar['nilai'];
		}
        
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_mutubuahht where divisi like '" . $div . "%' and tanggal='" . $tgl . "' order by tanggal desc, divisi asc ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
        echo"<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=left>" . $bar['blok'] . "</td>
                <td align=right>" . @number_format($bar['totaljjg'], 2) . "</td>";
		foreach($arrKriteria as $key=>$val)
		{
			$jjg = $arrnilaikriteria[$bar['blok']][$key];
			echo"<td align=right>".@number_format($jjg, 2)."</td>";
			
			$arrTotal[$key] += $jjg;
		}
                // <td align=right>" . @number_format($bar['buahmatang'], 2) . "</td>
                // <td align=right>" . @number_format($bar['buahmentah'], 2) . "</td>
                // <td align=right>" . @number_format($bar['kurangmatang'],2) . "</td>
                // <td align=right>" . @number_format($bar['lewatmatang'], 2) . "</td>
                // <td align=right>" . @number_format($bar['jjgkosong'],2) . "</td>
                // <td align=right>" . @number_format($bar['tangkaipanjang'],2) . "</td>
		echo"<td align=center>
                <img src=images/application/application_delete.png class=resicon  title='Delete' 
                onclick=\"deletedetail('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['blok'] . "');\" ></td>";
                @$ttotaljjg+=$bar['totaljjg'];
        }
        echo"<tr class=rowcontent>
                <td align=right colspan=2><b>" . $_SESSION['lang']['total'] . "</td>
                <td align=right><b>" . @number_format($ttotaljjg, 2) . "</td>";
		foreach($arrKriteria as $key=>$val)
		{
			echo"<td align=right><b>" . @number_format($arrTotal[$key], 2) . "</td>";
		}
		echo"<td ></td>
                </tr>
        </table>";
    break;

    case'insert':
        #cek data
        $str = "select count(*) as jmlhrow from ".$dbname.".kebun_mutubuahht where divisi='".$div."' and tanggal='".$tgl."' and blok='".$blok."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
        $jlhbrs = $bar['jmlhrow'];
        if ($jlhbrs > 0) 
		{
            exit("Error : Data untuk blok : " . $blok . " ditanggal " . tanggalnormal($tgl) . " sudah ada.");
        }
        
        $str = "insert into ".$dbname.".kebun_mutubuahht (`divisi`, `tanggal`, `blok`, `totaljjg`,`updateby`)
		values ('".$div."','".$tgl."','".$blok."','".$totaljjg."','".$_SESSION['standard']['userid']."')";

		try 
		{
            $owlPDO->exec($str);
			foreach($arrdeckriteria as $key)
			{
				$nilai = checkPostGet("kriteria_".$key,"0");
				$str2 = "insert into ".$dbname.".kebun_mutubuahdt (blok,tanggal,jenismutu,nilai) value ('".$blok."','".$tgl."','".$key."','".$nilai."')";
				
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

        $str = "delete from ".$dbname.".kebun_mutubuahht where divisi='".$div."' and tanggal='".$tgl."' and blok='".$blok."'";
        try 
		{
            $owlPDO->exec($str);
			$str2 = "delete from ".$dbname.".kebun_mutubuahdt where tanggal='".$tgl."' and blok='".$blok."'";
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
		##Get Kriteria Mutu
		$arrKriteria = array();
		$str = "select idjenis, kriteria from ".$dbname.".kebun_5jenismutu where jenis='Mutu Buah' order by idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrKriteria[$bar['idjenis']] = $bar['kriteria'];
		}
	
        $where = "";
        $where.=" and divisi like '" . $_SESSION['empl']['lokasitugas'] . "%' ";
        if ($divsch != '') {
            $where.=" and divisi='" . $divsch . "' ";
            $where2.=" and blok like '".$divsch."%'";
        }
        if ($tglsch != '') {
            $where.=" and tanggal='" . $tglsch . "' ";
            $where2.=" and tanggal='".$tglsch."'";
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
		
		##Get Detail Kriteria
		$arrnilaikriteria = array();
		$str = "select * from ".$dbname.".kebun_mutubuahdt where 1=1 ".$where2."";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) 
		{
			$arrnilaikriteria[$bar['jenismutu']] += $bar['nilai'];
		}

        $sql = "select count(*) as jmlhrow from " . $dbname . ".kebun_mutubuahht where 1=1 " . $where . " group by divisi,tanggal";
        $res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = owlBaris($res);
        $no = 0;

        $str = "SELECT *,sum(totaljjg) as totaljjg FROM " . $dbname . ".kebun_mutubuahht
        where 1=1 " . $where . " group by divisi,tanggal order by tanggal desc,divisi asc limit " . $offset . "," . $limit . "";
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
            $tab.="<td  align=right width=65px>" . @number_format($bar['totaljjg'], 2) . "</td>";
			foreach($arrKriteria as $key=>$val)
			{
				$tab.="<td  align=right width=65px>".@number_format($arrnilaikriteria[$key], 2)."</td>";
			}
            if ($bar['posting'] == 0) {
                $isi.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                    onclick=\"edit('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";
                $isi.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                    onclick=\"del('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "');\" ></td>";  
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
                    $isi.="<td align=center></td><td align=center></td>";
                    $isi.="<td align=center><img src=".$icon." class=resicon class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $isi.="<td align=center><img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"htmldt('" . $bar['divisi'] . "','" . tanggalnormal($bar['tanggal']) . "','html',event);\" ></td>";

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
                     <tr><td colspan=14 align=center>";

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

    case'posting':
		$str = "update ".$dbname.".kebun_mutubuahht set posting='1',postingdate='".date('Y-m-d')."',postingby='".$_SESSION['standard']['userid']."' where divisi like '".$div."%' and tanggal='".$tgl."' ";

        try 
		{
            $owlPDO->exec($str);
        }
		catch (PDOException $e) 
		{
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case'unposting':

        $str = "update ".$dbname.".kebun_mutubuahht set posting='0',postingdate='".date('Y-m-d')."',postingby='".$_SESSION['standard']['userid']."' where divisi like '".$div."%' and tanggal='".$tgl."'";
        try 
		{
			$owlPDO->exec($str);
        } 
		catch (PDOException $e) 
		{
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case'delete':

        $str = "delete from ".$dbname.".kebun_mutubuahht where divisi='".$div."' and tanggal='".$tgl."'";
        try 
		{
            $owlPDO->exec($str);
			$str2 = "delete from ".$dbname.".kebun_mutubuahdt where blok like '".$div."%' and tanggal='".$tgl."'";
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

    case'html':
		echo "<link rel=stylesheet type='text/css' href='style/".$gen."'>";
        if($tipe=='excel')
        {
            $border="border=1";
        }
        else
        {
			$border="border=0";
			 echo" Print Excel : <img style=cursor:pointer; "
				. " onclick=\"parent.htmldt('" .$div. "','" .tanggalnormal($tgl)."','excel',event)\" src=images/excel.jpg title='MS.Excel'> <br> <br>";
        }
		
		##Get Kriteria Mutu
		$arrKriteria = array();
		$str = "select idjenis, kriteria, satuan from ".$dbname.".kebun_5jenismutu where jenis='Mutu Buah' order by idjenis asc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) 
		{
            $arrKriteria[$bar['idjenis']]['keterangan'] = $bar['kriteria'];
            $arrKriteria[$bar['idjenis']]['satuan'] = $bar['satuan'];
        }
		
		$tab = "<table $border cellpadding=1 cellspacing=1 class=sortable width=100%>
                <thead>
                <tr class=rowheader>
                    <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>    
                    <td align=center rowspan='2'>".$_SESSION['lang']['blok']."</td>
                    <td align=center rowspan='2'>" . $_SESSION['lang']['total'] . " ".$_SESSION['lang']['jjg']."</td>";
		foreach($arrKriteria as $key=>$val)
		{
			$tab.="<td align=center colspan='2'>".$val['keterangan']."</td>";
		}
		$tab.="</tr>
                <tr>";
		foreach($arrKriteria as $key=>$val)
		{
			$tab.="<td align=center>".$val['satuan']."</td>
				<td align=center>%</td>";
		}
		$tab.="</tr>
                </thead>";
				
				##Get Detail Kriteria
				$arrnilaikriteria = array();
				$str = "select * from ".$dbname.".kebun_mutubuahdt where blok like '".$div."%' and tanggal='".$tgl."'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) 
				{
					$arrnilaikriteria[$bar['blok']][$bar['jenismutu']] = $bar['nilai'];
				}
				
                $no = 0;
                $str = "select * from " . $dbname . ".kebun_mutubuahht where divisi like '" . $div . "%' and tanggal='" . $tgl . "' ";

                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while ($bar = $res->fetch()) {
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>" . $no . "</td>
                           <td align=left>" . $bar['blok'] . "</td>
                           <td align=right>" . @number_format($bar['totaljjg'], 2) . "</td>";
					
					$jjg = 0;
					$persenjjg = 0;
					foreach($arrKriteria as $key=>$val)
					{
						$jjg = $arrnilaikriteria[$bar['blok']][$key];
						$persenjjg = ($arrnilaikriteria[$bar['blok']][$key]/$bar['totaljjg'])*100;
						$tab.="<td align=right>".@number_format($jjg, 2)."</td>";
						$tab.="<td align=right>".@number_format($persenjjg, 2)."</td>";
						
						$arrTotal[$key] += $jjg;
					}
					
                    @$ttotaljjg+=$bar['totaljjg'];
                }
            $tab.="</tr>";
            $tab.="<tr class=rowcontent>
                    <td align=left colspan=2><b>" . $_SESSION['lang']['total'] . "</td>
                    <td align=right><b>" . @number_format($ttotaljjg, 2) . "</td>";
			foreach($arrKriteria as $key=>$val)
			{
				$tab.="<td align=right><b>" . @number_format($arrTotal[$key], 2) . "</td>";
				$tab.="<td align=right><b>" . @number_format((($arrTotal[$key]/$ttotaljjg)*100), 2) . "</td>";
			}
            $tab.="</tr>";
            $tab.="</table>";

    if ($tipe=='excel'){

        $stream="Tanggal : ".tanggalnormal($tgl)."<br> 
                 Divisi  : ".$div;
        echo $stream.=$tab;  
        $nop_ = "Laporan_Mutu_buah" . date('Ymd_His');
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

    case'excel':

        $tab.= "<table cellpadding=1 cellspacing=1 border=1 class=sortable  style=width:100%>
                <thead>
                <tr class=rowheader>    
                    <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                    <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                    <td align=center>" . $_SESSION['lang']['blok'] . "</td>
                    <td align=center>" . $_SESSION['lang']['total'] . " ".$_SESSION['lang']['jjg']."</td>
                    <td align=center>".$_SESSION['lang']['buah']."<br>".$_SESSION['lang']['matang']."</td>
                    <td align=center>".$_SESSION['lang']['buah']."<br>".$_SESSION['lang']['mentah']."</td>
                    <td align=center>".$_SESSION['lang']['kurang']."<br>".$_SESSION['lang']['matang']."</td>
                    <td align=center>".$_SESSION['lang']['lewat']."<br>".$_SESSION['lang']['matang']."</td>
                    <td align=center>".$_SESSION['lang']['jjg']."<br>".$_SESSION['lang']['kosong']."</td>
                    <td align=center>".$_SESSION['lang']['tangkai']."<br>".$_SESSION['lang']['panjang']."</td>
                </tr>
                </thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_mutubuah where divisi like '" . $unitexp . "%' "
                . " and tanggal like '" . $perexp . "%' order by tanggal asc,blok asc ";
 
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td >" . tanggalnormal($bar['tanggal']) . "</td>";
                $tab.="<td>" . $bar['blok'] . "</td>";
                $tab.="<td  align=right>" . @number_format($bar['totaljjg'], 2) . "</td>";
                $tab.="<td  align=right>" . @number_format($bar['buahmatang'],2) . "</td>";
                $tab.="<td  align=right>" . @number_format($bar['buahmentah'],2) . "</td>";
                $tab.="<td  align=right>" . @number_format($bar['kurangmatang'],2) . "</td>";
                $tab.="<td  align=right>" . @number_format($bar['lewatmatang'],2) . "</td>";
                $tab.="<td  align=right>" . @number_format($bar['jjgkosong'],2) . "</td>";
                $tab.="<td  align=right>" . @number_format($bar['tangkaipanjang'],2) . "</td>";
                $div=$bar['divisi'];                    
                }
            $tab.="</tr>";
            $tab.="</table>";

        $stream="Unit  : ".$unitexp." <br> ";
        $stream.="Divisi  : ".$div." <br> ";
        $stream.= $tab;
        $nop_ = "Laporan_Mutu_Buah_divisi_".$div."_". date('Ymd_His');
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