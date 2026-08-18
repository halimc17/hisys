<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt = $_POST['pt'];
$gudang = isset($_POST['gudang']) ? $_POST['gudang'] : '';
$periode = isset($_POST['periode']) ? $_POST['periode'] : '';

$chksaldoawal = checkPostGet('chksaldoawal','');
$chkmasuk = checkPostGet('chkmasuk','');
$chkkeluar = checkPostGet('chkkeluar','');
$chksaldo = checkPostGet('chksaldo','');

if ($periode == '' and $gudang == '') {
    $str = "select a.kodebarang,sum(a.saldoqty) as kuan, 
	      b.namabarang,b.satuan,a.kodeorg from " . $dbname . ".log_5masterbarangdt a
		  left join " . $dbname . ".log_5masterbarang b
		  on a.kodebarang=b.kodebarang
		  where kodeorg='" . $pt . "' and a.saldoqty!=0
                  group by a.kodeorg,a.kodebarang order by kodebarang";
} else if ($periode == '' and $gudang != '') {
    $str = "select a.kodebarang,sum(a.saldoqty) as kuan, 
	      b.namabarang,b.satuan from " . $dbname . ".log_5masterbarangdt a
		  left join " . $dbname . ".log_5masterbarang b
		  on a.kodebarang=b.kodebarang
		  where kodeorg='" . $pt . "' 
		  and kodegudang='" . $gudang . "' and a.saldoqty!=0
		  group by a.kodeorg,a.kodebarang  order by kodebarang";
} else {
    if ($gudang == '') {
        $str = "select 
			  a.kodeorg,
			  a.kodebarang,
			  sum(a.saldoakhirqty) as salakqty,
			  sum(a.qtymasuk) as masukqty,
			  sum(a.qtykeluar) as keluarqty,
			  sum(a.saldoawalqty) as sawalqty,
		      b.namabarang,b.satuan    
		      from " . $dbname . ".log_5saldobulanan a
		      left join " . $dbname . ".log_5masterbarang b
			  on a.kodebarang=b.kodebarang
			  where kodeorg='" . $pt . "' 
			  and periode='" . $periode . "' and (a.qtymasuk!=0 or  a.qtykeluar!=0 or a.saldoakhirqty!=0)
			  group by a.kodebarang order by a.kodebarang";
    } else {
        $str = "select
			  a.kodeorg,
			  a.kodebarang,
			  sum(a.saldoakhirqty) as salakqty,
			  sum(a.qtymasuk) as masukqty,
			  sum(a.qtykeluar) as keluarqty,
			  sum(a.saldoawalqty) as sawalqty,
		      b.namabarang,b.satuan  		 		      
			  from " . $dbname . ".log_5saldobulanan a
		      left join " . $dbname . ".log_5masterbarang b
			  on a.kodebarang=b.kodebarang
			  where kodeorg='" . $pt . "' 
			  and periode='" . $periode . "'
			  and kodegudang='" . $gudang . "' and (a.qtymasuk!=0 or  a.qtykeluar!=0 or a.saldoakhirqty!=0)
			  group by a.kodebarang order by a.kodebarang";
    }
}
//=================================================
// echo $str;
if ($periode == '') {
    $sawalQTY = '';
    $masukQTY = '';
    $keluarQTY = '';
    $kuantitas = 0;
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_OBJ);
	//$numrows=owlBaris($res);
	//exit("Error:MASUK");
    $no = 0;
    if ($numrows < 1) {
        echo"<tr class=rowcontent><td colspan=12>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
    } else {
        while ($bar = $res->fetch()) {
			$strmin="select stok from ".$dbname.".log_5minimunstok where gudang='".$gudang."' and kodebarang='".$bar->kodebarang."'";
			$resmin=fetchdata($strmin);
			$stokmin = ($resmin[0]['stok']==''?0:$resmin[0]['stok']);
			$vstokmin = "";
			if($stokmin > 0){
				$vstokmin = ($stokmin==0?'':$stokmin);						
			}
			
			
            $no+=1;
            $periode = date('Y-m-d H:i:s');
            $kodebarang = $bar->kodebarang;
            $namabarang = $bar->namabarang;
            $kuantitas = $bar->kuan;
            echo"<tr class=rowcontent  style='cursor:pointer;' title='Click' onclick=\"detailMutasiBarang(event,'" . $pt . "','" . $periode . "','" . $gudang . "','" . $kodebarang . "','" . $namabarang . "','" . $bar->satuan . "');\">
				  <td align=center >" . $no . "</td>
				  <td align=center >" . $pt . "</td>
				  <td >" . $gudang . "</td>
				  <td align=center >" . $periode . "</td>
				  <td align=center >" . $kodebarang . "</td>
				  <td >" . $namabarang . "</td>
				  <td align=center >" . $bar->satuan . "</td>
				  <td align=center >" . getNamaBrg($kodebarang,'jenis') . "</td>
				   <td align=right >" . hidezerodecimal($vstokmin,2) . "</td>
				   <td align=right >" . $sawalQTY . "</td>
				   <td align=right >" . $masukQTY . "</td>
				   <td align=right >" . $keluarQTY . "</td>";
            if (substr($kodebarang, 0, 3) == '312') {
                echo"<td align=right class=firsttd >" . number_format($kuantitas, 3, '.', ',') . "</td>";
            } else {
                echo"<td align=right class=firsttd >" . number_format($kuantitas, 2, '.', ',') . "</td>	";
			}
			echo"		   			
				</tr>";
        }
    }
}
else {
    $salakqty = 0;
    $masukqty = 0;
    $keluarqty = 0;
    $sawalQTY = 0;
	
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
    $no = 0;
    if ($numrows < 1) {
        echo"<tr class=rowcontent><td colspan=11>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
    } else {
        while ($bar = $res->fetch()) {
            $kodebarang = $bar->kodebarang;
            $namabarang = $bar->namabarang;


            $salakqty = $bar->salakqty;
            $masukqty = $bar->masukqty;
            $keluarqty = $bar->keluarqty;
            $sawalQTY = $bar->sawalqty;
			
			$showhide = "hide";
            $showhide2 = "hide";
            $showhide3 = "hide";
            $showhide4 = "hide";
			if($chksaldoawal == '1'){
				$showhide = 'show';
			}else{
				if(number_format($sawalQTY,3) <= 0){
					$showhide = 'hide';
				}else{
					$showhide = 'show';
				}
			}
			
			if($chkmasuk == '1'){
				$showhide2 = 'show';
			}else{
				if(number_format($masukqty,3) <= 0){
					$showhide2 = 'hide';
				}else{
					$showhide2 = 'show';
				}
			}
			
			if($chkkeluar == '1'){
				$showhide3 = 'show';
			}else{
				if(number_format($keluarqty,3) <= 0){
					$showhide3 = 'hide';
				}else{
					$showhide3 = 'show';
				}
			}
			
			if($chksaldo == '1'){
				$showhide4 = 'show';
			}else{
				if(number_format($salakqty,3) <= 0){
					$showhide4 = 'hide';
				}else{
					$showhide4 = 'show';
				}
			}

			if($showhide=='show' && $showhide2=='show' && $showhide3=='show' && $showhide4=='show'){
				$no+=1;
				echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"detailMutasiBarang(event,'" . $pt . "','" . $periode . "','" . $gudang . "','" . $kodebarang . "','" . $namabarang . "','" . $bar->satuan . "');\">
				  <td align=center style='width:50px;'>" . $no . "</td>
				  <td align=center >" . $pt . "</td>
				  <td >" . $gudang . "</td>
				  <td align=center >" . $periode . "</td>
				  <td align=center >" . $kodebarang . "</td>
				  <td >" . $namabarang . "</td>
				  <td align=center >" . $bar->satuan . "</td>
				  <td align=center >" . getNamaBrg($kodebarang,'jenis') . "</td>
				  <td align=right ></td>
				  ";
				if (substr($kodebarang, 0, 3) == '312') {
					echo "<td align=right class=firsttd  >" . number_format($sawalQTY, 3, '.', ',') . "</td>
				   <td align=right class=firsttd  >" . number_format($masukqty, 3, '.', ',') . "</td>
				   <td align=right class=firsttd >" . number_format($keluarqty, 3, '.', ',') . "</td>
				   <td align=right class=firsttd >" . number_format($salakqty, 3, '.', ',') . "</td>
				   <td align=center></td>";
				} else
					echo "<td align=right class=firsttd >" . number_format($sawalQTY, 2, '.', ',') . "</td>
				   <td align=right class=firsttd >" . number_format($masukqty, 2, '.', ',') . "</td>
				   <td align=right class=firsttd >" . number_format($keluarqty, 2, '.', ',') . "</td>
				   <td align=right class=firsttd >" . number_format($salakqty, 2, '.', ',') . "</td>
				   <td align=center></td>
				</tr>";
			}
        }
    }
}
?>