<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

    $theme=$_SESSION['theme'];
    if($theme=='skyblue' || $theme==''){
      $gen='generic.css';
    }else if($theme=='red'){
      $gen='genericRed.css';  
    }else{
      $gen='genericGray.css';  
    }  
$tgl = checkPostGet('tgl', '');
$blok = checkPostGet('blok', '');
$tipe = checkPostGet('tipe', '');
$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
echo"
<link rel=stylesheet type=text/css href=style/".$gen.">	
";

if ($tipe == 'excel') {
    $border = "border=1";
} else {
    $border = "border=0";
}

switch($proses)
{
	case'RekapPanen':
	
	echo" Print Excel : <img style=cursor:pointer; "
	 . " onclick=\"parent.lihatdetail('" . $blok . "','" . $tgl . "','excel','RekapPanen',event)\" src=images/excel.jpg  
		title='MS.Excel'>
	   ";	
	
	$stream = "<table cellpadding=1 cellspacing=1 ".$border." class=sortable style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center rowspan='2'>TK</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . " " . $_SESSION['lang']['panen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['afkir'] . "</td>
        
        </tr>
        <tr>
            <td align=center>" . $_SESSION['lang']['luasareaproduktif'] . "</td>
            <td align=center>" . $_SESSION['lang']['panen'] . "</td>
            <td align=center>" . $_SESSION['lang']['jjg'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".kebun_rekappnn_vw where blok = '" . $blok . "' and tanggal='" . $tgl . "' ";

        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()) {
            $no+=1;
            $stream.="<tr class=rowcontent>";
            $stream.="<td align=center>" . $no . "</td>";
            $stream.="<td align=left>" . $bar['blok'] . "</td>";
            $stream.="<td align=right>" . $bar['tahuntanam'] . "</td>";
            $stream.="<td align=right>" . @number_format($bar['luasproduksi'], 2) . "</td>";
            $stream.="<td align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
            $stream.="<td align=right>" . @number_format($bar['tenagakerja'], 2) . "</td>";
            $stream.="<td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
            $stream.="<td align=right>" . @number_format($bar['bjr'], 2) . "</td>";
            $stream.="<td align=right>" . @number_format($bar['kgkebun']) . "</td>";
            $stream.="<td align=right>" . @number_format($bar['jjgafkir']) . "</td>";
            $stream.="<td align=left>" . $bar['keterangan'] . "</td>";
            
        }
        
        $stream.="</table>";

	break;
	
	case'SPBTBS':
	
	echo" Print Excel : <img style=cursor:pointer; "
	 . " onclick=\"parent.lihatdetail('" . $blok . "','" . $tgl . "','excel','SPB TBS',event)\" src=images/excel.jpg  
		title='MS.Excel'>
	   <br><b>" . $_SESSION['lang']['blok'] . " : " . $blok."</b>
	   ";
	
	$stream = "<table " . $border . " class=sortable cellspacing=1 style=width:100%>
				 <thead>
					<tr>
					  <td align=center>No</td>    
					    
					  <td align=center>" . $_SESSION['lang']['nospb'] . "</td>    
					  <td align=center>" . $_SESSION['lang']['jjg'] . "</td> 
					</tr>  
				 </thead>
				<tbody id=container>";
	
	$no = 0;
	$str = "select * from " . $dbname . ".kebun_spb_vw where blok='" . $blok . "' and tanggalpanen='" . $tgl . "'  ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$no+=1;
		$stream.="<tr class=rowcontent>
			<td align=center>" . $no. "</td>   
			  
			<td align=left>" . $bar['nospb'] . "</td>   \
			<td align=right>" . number_format($bar['jjg']) . "</td>   
			 </tr>";
			 @$tjjg+=$bar['jjg'];
			 
	}
			$stream.="<tr class=rowcontent>
				<td align=center colspan=2>TOTAL</td> 
				<td align=right>".number_format($tjjg)."</td>  
			</tr>";
			
	break;
	
}

if ($tipe == 'excel') {
    //echo $stream;
    //$stream.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
    $nop_ = "detail_transaksi ".$proses. _ . $blok . _ . $tgl;
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
                parent.window.alert('Can't convert to excel format');
                </script>";
            exit;
        } else {
            echo "<script language=javascript1.2>
                window.location='tempExcel/" . $nop_ . ".xls';
                </script>";
        }
        fclose($handle);
    }
} else {
    echo $stream;
}
?>