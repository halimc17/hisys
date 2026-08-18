<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

	$proses  = checkPostGet('proses','');//$_POST['proses'];
	$kdOrg 	 = checkPostGet('kdOrg','');
	$tgl 	 = tanggaldb(checkPostGet('tgl',''));
    $divisi	 = checkPostGet('divisi','');

    switch($proses){
        case 'gantidivisi':
			
			//echo "1<br>";
			$lksi 	= substr($_SESSION['empl']['lokasitugas'],0,4);
			$sKbn 	= "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$kdOrg."') or (tipe = 'AFDELING' and kodeorganisasi like '".$kdOrg."%') order by kodeorganisasi ";
			$qKbn 	= $owlPDO->query($sKbn) or die(print " Gagal: ".PDOException::getMessage());
			$qKbn->setFetchMode(PDO::FETCH_ASSOC);
			$optKbn="<option value=''>".$_SESSION['lang']['all']."</option>";
			while($rKbn = $qKbn->fetch()) {
				if(strlen($rKbn['kodeorganisasi']) > 4){
					$optKbn .= "<option value=".$rKbn['kodeorganisasi'].">".$rKbn['namaorganisasi']."</option>";
				}
			}

			echo $optKbn;
		break;

		case 'getData':
			$whrUnit = "";            
            if ($kdOrg != '' || $divisi != '') {
                if ($kdOrg != '') {
                    $whrUnit = "and c.divisi like '".$kdOrg."%'";
                }
                if ($divisi != '') {
                    $whrUnit = "and c.divisi='".$divisi."'";
                }
            }
            $sKebun = "Select ifnull(b.nospb,a.noreferensi) as flagrestan, c.kodeorg, c.divisi, a.tph,
                        a.hasilkerja as jjg,a.brondolan,c.tanggal,d.tanggal,
                        datediff(d.tanggal,c.tanggal) as diff
                        From ".$dbname.".kebun_prestasi_mobile a
                        Left join ".$dbname.".kebun_spbdt_mobile b on
                        b.nopnnref = a.noreferensi and
                        b.nik = a.nik and
                        b.tph = a.tph and
                        b.sesi = a.sesi
                        Left join ".$dbname.".kebun_spbht_mobile d on b.nospb = d.nospb
                        Left join ".$dbname.".kebun_aktifitas_mobile c on a.notransaksi =c.notransaksi
                        Where c.tanggal like '".$tgl."' and (datediff(d.tanggal,c.tanggal) != 0 or d.tanggal is null) and c.tipetransaksi = 'PNN' ".$whrUnit." order by diff";
			$qKebun 	= $owlPDO->query($sKebun) or die(print " Gagal: ".PDOException::getMessage());
			$qKebun->setFetchMode(PDO::FETCH_ASSOC);
			$rowKebun 	= owlBaris($qKebun);
            $jml['jjg'] = 0;
            $jml['brondolan'] = 0;
            $totJJG = 0;
            $totBRD = 0;
            $stream = "<fieldset>
						    <legend>".$_SESSION['lang']['list']."</legend>
                            <table cellspacing=1 border=0 class=sortable width=100%>
                                <thead>
                                    <tr class=rowheader>
                                        <th>".@$_SESSION['lang']['nourut']."</th>
                                        <th>".@$_SESSION['lang']['nospb']." Mobile / Docket Panen</th>
                                        <th>".@$_SESSION['lang']['divisi']."</th>
                                        <th>".@$_SESSION['lang']['tph']."</th>
                                        <th>".@$_SESSION['lang']['jjg']."</th>
                                        <th>".@$_SESSION['lang']['brondolan']."</th>
                                        <th>".@$_SESSION['lang']['restan']."</th>
                                    </tr>
                                </thead>
                            <tbody>";
			$no	= 1;
            if ($qKebun->fetch()) {
                $pembeda = "";
                $data = array();
                $allTPH = array();
                while ($r = $qKebun->fetch()){
                    $data[] = $r;
                    $allTPH[] =$r['tph'];
                }
                $allTPH = array_unique($allTPH);
                $getTPH = "select kode,latitude,logitude from ".$dbname.".kebun_5tph where kode in ('".implode("','",$allTPH)."')";
                $dataTPH = fetchData($getTPH);
                $allTPH = array();
                foreach($dataTPH as $v){
                    $allTPH[$v['kode']] = $v['latitude'].",".$v['logitude'];
                }
                //while ($row = $qKebun->fetch()){
                foreach($data as $k=>$row){
                    if(@$allTPH[$row['tph']] != ""){
                        $urlGmap = 'onclick="window.open(\'https://maps.google.com/?q='.@$allTPH[$row['tph']].'\', \'_blank\');"';
                    }else{
                        $urlGmap = 'onclick="alert(\'Latitude dan longitud TPH belum tersedia!\');"';
                    }
                    
                    $cekRestan = ($row['diff'] == null) ? '<td style="background-color:red;color:white;cursor:pointer;" latlong="'.@$allTPH[$row['tph']].'" '. $urlGmap.'>Belum di angkut </td>' : '<td>'.$row['diff'] . ' Hari </td>';
                    if($pembeda != (($row['diff'] == null)?'NULL':$row['diff'])){
                        if($pembeda != ""){
                            $stream .="<tr align=center class=rowcontent><td colspan=\"4\"> Total</td><td align=center>" . number_format($jml['jjg']) . "</td><td align=center>" . number_format($jml['brondolan']) . "</td><td></td></tr>";
                        }
                        $pembeda = ($row['diff'] == null)?'NULL':$row['diff'];
                        $jml['jjg'] = 0;
                        $jml['brondolan'] = 0;
                    }
                    
                    $stream .=  "<tr align=center class=rowcontent>
                                    <td>" . $no++. "</td>
                                    <td>" . $row['flagrestan'] . "</td>
                                    <td>" . $row['divisi'] . "</td>
                                    <td>" . $row['tph'] . "</td>
                                    <td>" . number_format($row['jjg']) . "</td>
                                    <td>" . number_format($row['brondolan']) . "</td>
                                    $cekRestan 
                                </tr>";
                    $jml['jjg']+=$row['jjg'];
                    $jml['brondolan']+= $row['brondolan'];
                    $totJJG += $row['jjg'];
                    $totBRD += $row['brondolan'];
                }
                $stream .=      "<tr class=rowcontent><td colspan=\"4\"> Total</td><td align=center>" . number_format($jml['jjg']) . "</td><td align=center>" . number_format($jml['brondolan']) . "</td><td></td></tr>";
                $stream .=      "<tr class=rowcontent><td colspan=\"4\">Grand Total</td><td align=center>" . number_format($totJJG). "</td><td align=center>" . number_format($totBRD) . "</td><td></td></tr>";
            } else {
                $stream .=      "<tr align=center class=rowcontent><td colspan='7'>Tidak ada data</td></tr>";
            }
            
            $stream .=          "<tr class=rowcontent id=row style=''></tr>";
			$stream .=      "</tbody>";
			
			$stream .=      "
						</table>
					</fieldset>";
			echo $stream;
	    break;
		
        default:
        break;
    }
?>