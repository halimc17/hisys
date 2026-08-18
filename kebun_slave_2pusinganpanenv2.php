<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$kdorg = checkPostGet('kdorg', '');
$tgl1 = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));

switch($method){
	case'preview':

        $month1 = substr($tgl1, 5, 2); // Extracts 
        $month2 = substr($tgl2, 5, 2); // Extracts 

        if ($month1 != $month2) {
            exit("Warning : Bulan pada tanggal pertama dan tanggal kedua harus sama" . $variable);
        }
        
		$exptglakhir=explode('-',$tgl2);
		$tglakhir=$exptglakhir[2];


		$rangetgl = rangeTanggalarr($tgl1,$tgl2);
		$tab="";				
		if($tipeprint=='html'){
			$border="border=0";
		}else{
			$border="border=1";
		}

		$str = "select * from ".$dbname.".setup_blok where  indukblok like '".$kdorg."%' and luasareaproduktif >'0'";
		$res = fetchdata($str);
        foreach($res as $val){
				$Luas[$val['indukblok']] += $val['luasareaproduktif'];
				$Pokok[$val['indukblok']] += $val['jumlahpokok'];
        }

        $str = "select a.*,b.luasareaproduktif from ".$dbname.".kebun_pusingan a
        left join ".$dbname.".setup_blok b on a.blok=b.indukblok 
        where  a.blok like '".$kdorg."%' and a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'";
		$res = fetchdata($str);
        foreach($res as $val){
            $kdblok[$val['blok']] = $val['blok'];
            $kddivisi[$val['blok']] = substr($val['blok'],0,6);

            $angka[substr($val['blok'],0,6)][$val['blok']][$val['tanggal']] = $val['angka'];
            $ket[substr($val['blok'],0,6)][$val['blok']][$val['tanggal']] = $val['keterangan'];
        }

        $hapnn=$jjgpnn=array();
        $str = "select * from " . $dbname . ".kebun_rekappnn   where divisi like '" . $kdorg . "%' ".$whd." and tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' ";
        $res=fetchdata($str);
        foreach($res as $bar){
            $hapnn[$bar['blok']][$bar['tanggal']] += $bar['luaspanen'];
            $jjgpnn[$bar['blok']][$bar['tanggal']] += $bar['jjgpanen'];
        }

		$colspn=$tglakhir;		
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
                <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['divisi'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['blok'] . "</th>
                <th align=center rowspan='2'>" . $_SESSION['lang']['luas'] . "</th>
                <th align=center rowspan='2'>Jumlah Pokok</th>        
                <th align=center rowspan='2'>SPH</th>      
				<th colspan='".$colspn."'>Tanggal</th>";
            $tab.="</tr>";
		$tab.="<tr class=rowheader style='text-align:center;font-weight:bold'>";
            foreach ($rangetgl as $listtanggal => $tgl) {
                $mggu = date('D', strtotime($tgl));
                if ($mggu == 'Sun') {
                    $tab.="<th align=center><font color=red>" . substr($tgl, 8, 2) . "</font></th>";
                } else {
                    $tab.="<th align=center>" . substr($tgl, 8, 2). "</th>";
                }
            }
		$tab.="</tr>";
		$tab.="</thead><tbody>";

        $no = 1;
        foreach($kdblok as $val){
            $tab.="<tr class='rowcontent'>";
                $tab.="<td align=center>" . $no++ . "</td>";
                $tab.="<td align=center>" . getNamaOrg($kddivisi[$val]) . "</td>";
                $tab.="<td align=center> (".getIndukBlok($val).") - " . $val . "</td>";
                $tab.="<td align=center>" . number_format($Luas[$val],2) . "</td>";
                $tab.="<td align=center>" . number_format($Pokok[$val]) . "</td>";
                $tab.="<td align=center>" . number_format($Pokok[$val]/$Luas[$val],2) . "</td>";

                @$ttluas +=$Luas[$val];
                @$ttpokok+=$Pokok[$val];

                foreach($rangetgl as $tgl){			
                    $ket[$kddivisi[$val]][$val][$tgl] = isset($ket[$kddivisi[$val]][$val][$tgl]) ? $ket[$kddivisi[$val]][$val][$tgl] : '';
                    $angka[$kddivisi[$val]][$val][$tgl] = isset($angka[$kddivisi[$val]][$val][$tgl]) ? $angka[$kddivisi[$val]][$val][$tgl] : '';

                    if ($ket[$kddivisi[$val]][$val][$tgl] == 'P' && $angka[$kddivisi[$val]][$val][$tgl] == '1') {
                        $bgcolor = "style=background-color:#067f02";
                    } else if ($ket[$kddivisi[$val]][$val][$tgl] == 'P' && $angka[$kddivisi[$val]][$val][$tgl] > '0') {
                        $bgcolor = "style=background-color:red";
                    } else {
                        $bgcolor = "";
                    }

                    $angka[$kddivisi[$val]][$val][$tgl] = isset($angka[$kddivisi[$val]][$val][$tgl]) ? $angka[$kddivisi[$val]][$val][$tgl] : '';


                    if($hapnn[$val][$tgl]>0){$hapanen="h:".number_format($hapnn[$val][$tgl],2);}else{$hapanen="";}
                    if($jjgpnn[$val][$tgl]>0){$jjgpanen="j:".number_format($jjgpnn[$val][$tgl]);}else{$jjgpanen="";}

                    $tab.="<td align=center " . $bgcolor . ">" . $angka[$kddivisi[$val]][$val][$tgl] . "
                            <br><font style=font-size:9px;>".$hapanen."</font>
                            <br><font style=font-size:9px;>".$jjgpanen."</font>
                        </td>";

                    $ttangka[$tgl] +=   $angka[$kddivisi[$val]][$val][$tgl] ;

                }
            $tab.="</tr>";
        }

        $tab.="
        <tr style=background-color:#00B366>
            <td align=center colspan=3><b>Total " . getNamaOrg($kdorg) . "</b></td>
            <td align=center><b>" . number_format($ttluas, 2) . "</b></td>
            <td align=center><b>" . number_format($ttpokok) . "</b></td>
            <td align=center><b>" . number_format($ttpokok / $ttluas,2) . "</b></td>";
            foreach($rangetgl as $tgl){		
                $tab.="<td align=center><b>" . number_format($ttangka[$tgl]) . " </b></td>";
            }
        $tab.="</tr>";


		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Pusingan_Panen_V2_".$kdorg."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$tab))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;
}


?>