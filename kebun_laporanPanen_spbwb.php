<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');

    // ambil yang dilempar javascript
	$pt = checkPostGet('pt','');
	$unit = checkPostGet('unit','');
	$afdeling = checkPostGet('afdeling','');
	$intiplasma = checkPostGet('intiplasma','');
	$tgl1 = checkPostGet('tgl1','');
	$tgl2 = checkPostGet('tgl2','');
    
    // olah tanggal
    $tanggal1=explode('-',$tgl1);
    $tanggal2=explode('-',$tgl2);
    $date1=$tanggal1[2].'-'.$tanggal1[1].'-'.$tanggal1[0];
    $tanggalterakhir=date('t', strtotime($date1));


    $namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

    // kamus blok
    $sdakar="select kodeorg,tahuntanam from ".$dbname.".setup_blok";
    $qdakar=$owlPDO->query($sdakar) or die(print " Gagal: ".PDOException::getMessage());
	$qdakar->setFetchMode(PDO::FETCH_ASSOC);
    while($rdakar=$qdakar->fetch())
    {
        $belok[$rdakar['kodeorg']]=$rdakar['tahuntanam'];
    }
    
    if($unit=='') // script copy-an dari kebun_laporanPanen.php
    {
		$where2 = "";
		if($afdeling!='')
		{
			$where2 = " and a.blok like '".$afdeling."%'";
		}
        else {
            $where2 = " and substr(a.blok,1,6) IN (".getOrgDetail(26).")";
        }
        $str="SELECT 
                a.blok,
                a.tanggal,
                a.tahuntanam,
                a.nospb,
                a.notiket,
                a.nokendaraan,
                a.jjg,
                a.kgwb,
                a.bjr,
                a.kgbjr,
                IF(b.intiplasma = 'I', 'Inti', 'Plasma') AS intiplasma
            FROM ".$dbname.".kebun_spb_vw a
            LEFT JOIN ".$dbname.".organisasi c 
                ON SUBSTR(a.kodeorg, 1, 4) = c.kodeorganisasi
            LEFT JOIN (
                SELECT DISTINCT indukblok, intiplasma 
                FROM ".$dbname.".setup_blok
            ) b ON a.blok = b.indukblok 
            where c.induk = '".$pt."' and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' and b.intiplasma like '%".$intiplasma."%'
            and a.posting=1 ".$where2."
            order by a.blok, a.tanggal";
    }
    else
    {
        $where='';
        if(!in_array($unit,getOrgDetail(28))){                
            $where=" and a.posting=1";
        }
		$where2 = "";
		if($afdeling!='')
		{
			$where2 = " and a.blok like '".$afdeling."%'";
		}
        else {
            $where2 = " and substr(a.blok,1,6) IN (".getOrgDetail(26).")";
        }
        $str="SELECT 
                a.blok,
                a.tanggal,
                a.tahuntanam,
                a.nospb,
                a.notiket,
                a.nokendaraan,
                a.jjg,
                a.kgwb,
                a.bjr,
                a.kgbjr,
                IF(b.intiplasma = 'I', 'Inti', 'Plasma') AS intiplasma
            FROM ".$dbname.".kebun_spb_vw a
            LEFT JOIN (
                SELECT DISTINCT indukblok, intiplasma 
                FROM ".$dbname.".setup_blok
            ) b 
            ON a.blok = b.indukblok 
            where a.blok like '".$unit."%'  and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' 
            ".$where." and b.intiplasma like '%".$intiplasma."%' ".$where2."
            order by a.blok, a.tanggal";
    }
    // header
    echo"<thead> 
        <tr>
            <th align=center>No.</th>
            <th align=center>".$_SESSION['lang']['afdeling']."</th>
            <th align=center>".$_SESSION['lang']['blok']."</th>
            <th align=center>".$_SESSION['lang']['intiplasma']."</th>
            <th align=center>".$_SESSION['lang']['tanggal']."</th>
            <th align=center>".$_SESSION['lang']['tahuntanam']."</th>
            <th align=center>".$_SESSION['lang']['nospb']."</th>
            <th align=center>".$_SESSION['lang']['noTiket']."</th>
            <th align=center>".$_SESSION['lang']['kendaraan']."</th>
            <th align=center>".$_SESSION['lang']['jjg']."</th>
            <th align=center>"."KG ".$_SESSION['lang']['kebun']."</th>    
            <th align=center>".$_SESSION['lang']['kgwb']."</th>
            <th align=center>".$_SESSION['lang']['bjr']." ".$_SESSION['lang']['aktual']."</th>
            <th align=center>".$_SESSION['lang']['bjr']." Kebun</th>
            <th align=center>%</th>
        </tr></thead>
	<tbody>";    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
    $no=0;
    if($numrows<1){
        $jukol=15;
        echo"<tr class=rowcontent><td colspan=".$jukol.">".$_SESSION['lang']['tidakditemukan']."</td></tr>";
    }else{
		$totalbarjjg=$totalbarkgbjr=$totalbarkgwb=0;
        while($bar=$res->fetch()){
        // content
        $no+=1;
        @$aktual=$bar->kgwb/$bar->jjg;
        echo"<tr class='rowcontent'>
            <td align=center>".$no."</td>
            <td align=left>".$namaOrg[substr($bar->blok,0,6)]."</td>
            <td align=center>".getIndukBlok($bar->blok)."</td>
            <td align=center>".$bar->intiplasma."</td>
            <td align=center>".$bar->tanggal."</td>
            <td align=center>".$bar->tahuntanam."</td>
            <td align=center>".$bar->nospb."</td>";
            $notiket=$bar->notiket;
            if($notiket!='')
            echo"<td align=right>".$notiket."</td>";else{
                echo"<td bgcolor=red title='Belum Masuk PKS' align=right>".$notiket."</td>";
            }
            echo"<td align=center>".$bar->nokendaraan."</td>
            <td align=right>".$bar->jjg."</td>";
            echo "<td align=right>".number_format($bar->kgbjr,2)."</td>";
            $kgwb=$bar->kgwb;
            if($kgwb!=0){
                echo"<td align=right>".number_format($kgwb,2)."</td>";
                $beda=$kgwb-$bar->kgbjr;
                @$persen=($beda/$bar->kgbjr)*100;
            }
            else{
                echo"<td bgcolor=red title='SPB Belum Diinput' align=right>".number_format($kgwb,2)."</td>";
                $persen=0;
            }
            echo"<td align=right>".number_format($aktual,2)."</td>
            <td align=right>".$bar->bjr."</td>";
            echo"<td align=right>".number_format($persen,2)."</td>";
            echo "</tr>";
			$totalbarjjg+=$bar->jjg;
            $totalbarkgbjr+=$bar->kgbjr;
            $totalbarkgwb+=$bar->kgwb;
        }
        echo"<tr class='rowcontent'>
            <td align=center></td>
            <td align=left></td>
            <td align=center></td>
            <td align=center></td>
            <td align=center></td>
            <td align=center></td>
            <td align=center>Total</td>";
//            $notiket=$bar->notiket;
//            if($notiket!='')
            echo"<td align=right></td>";
//            else{
//                echo"<td bgcolor=red title='Belum Masuk PKS' align=right>".$notiket."</td>";
//            }
            echo"<td align=center></td>
            <td align=right>".number_format($totalbarjjg)."</td>";
            echo "<td align=right>".number_format($totalbarkgbjr,2)."</td>";
//            $kgwb=$bar->kgwb;
//            if($kgwb!=0){
                echo"<td align=right>".number_format($totalbarkgwb,2)."</td>";
                $beda=$totalbarkgwb-$totalbarkgbjr;
                @$persen=($beda/$totalbarkgbjr)*100;
//            }
//            else{
//                echo"<td bgcolor=red title='SPB Belum Diinput' align=right>".number_format($kgwb,2)."</td>";
//                $persen=0;
//            }
        @$aktual=$totalbarkgwb/$totalbarjjg;
            echo"<td align=right>".number_format($aktual,2)."</td>
            <td align=right></td>";
            echo"<td align=right>".number_format($persen,2)."</td>";
            echo "</tr>";        
    } 
    echo"</tbody>
        <tfoot>
        </tfoot>";		 

?>