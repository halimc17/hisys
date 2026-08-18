<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$periode=$_GET['periode'];
$kodeorg=$_GET['kodeorg'];
if($periode=='')$periode=date('Y');    
$str3="select  sum(a.jlhbayar) as klaim, sum(a.totalklaim) as totalklaim, a.periode,a.kodebiaya,c.nama from ".$dbname.".sdm_pengobatanht a 
        left join ".$dbname.".sdm_5jenisbiayapengobatan c
        on a.kodebiaya=c.kode
        left join ".$dbname.".datakaryawan b 
        on a.karyawanid=b.karyawanid
              where a.periode like '".$periode."%'
              and b.lokasitugas like '".$kodeorg."%'
        group by kodebiaya,periode order by periode
    ";
	$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
	$res3->setFetchMode(PDO::FETCH_OBJ);
    $no=0;
    while($bar3=$res3->fetch())
    {
        $kode[$bar3->kodebiaya][$bar3->periode]=$bar3->klaim;
		$kode1[$bar3->kodebiaya][$bar3->periode] = $bar3->totalklaim;
        $kodex[$bar3->kodebiaya]['nama']=$bar3->nama;
    }
 $stream.="Biaya Pengobatan per jenis perawatan
     <table border=1>
    <thead>
    <tr class=rowheader>
       <td align=center rowspan=2>No</td>
        <td align=center rowspan=2>" . $_SESSION['lang']['kodeorg'] . "</td>
        <td align=center rowspan=2>" . $_SESSION['lang']['tahun'] . "</td>            
        <td align=center rowspan=2>Treatment Type</td>
        <td  align=center colspan=2>Jan</td>
        <td  align=center colspan=2>Feb</td>
        <td  align=center colspan=2>Mar</td>
        <td  align=center colspan=2>Apr</td>
        <td  align=center colspan=2>Mei</td>
        <td  align=center colspan=2>Jun</td>
        <td  align=center colspan=2>Jul</td>
        <td  align=center colspan=2>Aug</td>
        <td  align=center colspan=2>Sep</td>
        <td  align=center colspan=2>Oct</td>
        <td  align=center colspan=2>Nov</td>
        <td  align=center colspan=2>Dec</td>
        <td align=center colspan=2>" . $_SESSION['lang']['total'] . "</td>
    </tr>
	<tr>
	    <td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
		<td  align=center>" . $_SESSION['lang']['nilaiklaim'] . "</td>
	    <td  align=center>" . $_SESSION['lang']['dibayar'] . "</td>
	</tr>
    </thead>
    <tbody>";   
    
    foreach($kodex as $key=>$val){
        $no+=1;
        $total=$kode[$key][$periode."-12"]+$kode[$key][$periode."-11"]+$kode[$key][$periode."-10"]+$kode[$key][$periode."-09"]+$kode[$key][$periode."-08"]+$kode[$key][$periode."-07"]+$kode[$key][$periode."-06"]+$kode[$key][$periode."-05"]+$kode[$key][$periode."-04"]+$kode[$key][$periode."-03"]+$kode[$key][$periode."-02"]+$kode[$key][$periode."-01"];
        $gt+=$total;

		$total1 = $kode1[$key][$periode . "-12"] + $kode1[$key][$periode . "-11"] + $kode1[$key][$periode . "-10"] + $kode1[$key][$periode . "-09"] + $kode1[$key][$periode . "-08"] + $kode1[$key][$periode . "-07"] + $kode1[$key][$periode . "-06"] + $kode1[$key][$periode . "-05"] + $kode1[$key][$periode . "-04"] + $kode1[$key][$periode . "-03"] + $kode1[$key][$periode . "-02"] + $kode1[$key][$periode . "-01"];
		$gt1+=$total1;
        
 $stream.="<tr>
            <td>".$no."</td>
            <td>".$kodeorg."</td>
            <td>".$periode."</td>    
            <td>".$kodex[$key]['nama']."</td>                
            <td align=right>" . number_format($kode1[$key][$periode . "-01"]) . "</td>
			<td align=right>" . number_format($kode[$key][$periode . "-01"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-02"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-02"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-03"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-03"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-04"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-04"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-05"]) . "</td> 
            <td align=right>" . number_format($kode[$key][$periode . "-05"]) . "</td> 
            <td align=right>" . number_format($kode1[$key][$periode . "-06"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-06"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-07"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-07"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-08"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-08"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-09"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-09"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-10"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-10"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-11"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-11"]) . "</td>
            <td align=right>" . number_format($kode1[$key][$periode . "-12"]) . "</td>
            <td align=right>" . number_format($kode[$key][$periode . "-12"]) . "</td>
            <td align=right>" . number_format($total1) . "</td>
            <td align=right>" . number_format($total) . "</td>   
        </tr>";
        $t01+=$kode[$key][$periode."-01"];
        $t02+=$kode[$key][$periode."-02"];
        $t03+=$kode[$key][$periode."-03"];
        $t04+=$kode[$key][$periode."-04"];
        $t05+=$kode[$key][$periode."-05"];
        $t06+=$kode[$key][$periode."-06"];
        $t07+=$kode[$key][$periode."-07"];
        $t08+=$kode[$key][$periode."-08"];
        $t09+=$kode[$key][$periode."-09"];
        $t10+=$kode[$key][$periode."-10"];
        $t11+=$kode[$key][$periode."-11"];
        $t12+=$kode[$key][$periode."-12"]; 
		
		$t101+=$kode1[$key][$periode . "-01"];
		$t102+=$kode1[$key][$periode . "-02"];
		$t103+=$kode1[$key][$periode . "-03"];
		$t104+=$kode1[$key][$periode . "-04"];
		$t105+=$kode1[$key][$periode . "-05"];
		$t106+=$kode1[$key][$periode . "-06"];
		$t107+=$kode1[$key][$periode . "-07"];
		$t108+=$kode1[$key][$periode . "-08"];
		$t109+=$kode1[$key][$periode . "-09"];
		$t110+=$kode1[$key][$periode . "-10"];
		$t111+=$kode1[$key][$periode . "-11"];
		$t112+=$kode1[$key][$periode . "-12"];
    }
 $stream.="<tr class=rowcontent>
            <td colspan=4>Total</td>                
            <td align=right>" . number_format($t101) . "</td>
            <td align=right>" . number_format($t01) . "</td>
            <td align=right>" . number_format($t102) . "</td>
            <td align=right>" . number_format($t02) . "</td>
            <td align=right>" . number_format($t103) . "</td>
            <td align=right>" . number_format($t03) . "</td>
             <td align=right>" . number_format($t104) . "</td>
             <td align=right>" . number_format($t04) . "</td>
             <td align=right>" . number_format($t105) . "</td>
             <td align=right>" . number_format($t05) . "</td>
             <td align=right>" . number_format($t106) . "</td>
             <td align=right>" . number_format($t06) . "</td>
             <td align=right>" . number_format($t107) . "</td>
             <td align=right>" . number_format($t07) . "</td>
             <td align=right>" . number_format($t108) . "</td>
             <td align=right>" . number_format($t08) . "</td>
             <td align=right>" . number_format($t109) . "</td>
             <td align=right>" . number_format($t09) . "</td>
             <td align=right>" . number_format($t110) . "</td>
             <td align=right>" . number_format($t10) . "</td>
             <td align=right>" . number_format($t111) . "</td>
             <td align=right>" . number_format($t11) . "</td>
             <td align=right>" . number_format($t112) . "</td>     
             <td align=right>" . number_format($t12) . "</td>     
            <td align=right>" . number_format($gt1) . "</td>    
            <td align=right>" . number_format($gt) . "</td>     
        </tr>";  
 $stream.="</tbody>
    <tfoot>
    </tfoot>
    </table></div>
    </fieldset>";    
    
$nop_="Biaya pengobatan Per jenis Pengobatan-".$periode.$kodeorg;
if(strlen($stream)>0)
{
    if ($handle = opendir('tempExcel')) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
                @unlink('tempExcel/'.$file);
            }
        }	
        closedir($handle);
    }
    $handle=fopen("tempExcel/".$nop_.".xls",'w');
    if(!fwrite($handle,$stream))
    {
    echo "<script language=javascript1.2>
        parent.window.alert('Cant convert to excel format');
        </script>";
    exit;
    }
    else
    {
     
    echo "<script language=javascript1.2>
        window.location='tempExcel/".$nop_.".xls';
        </script>";
    }
    fclose($handle);
}	        
   
	 
?>
