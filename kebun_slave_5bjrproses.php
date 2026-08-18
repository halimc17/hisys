<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$kdKebun2=checkPostGet('kdKebun2','');
$bln=checkPostGet('bln','');
$tahun=checkPostGet('tahun','');
$periode1=checkPostGet('periode1','');
$periode2=checkPostGet('periode2','');

$blok=checkPostGet('blok','');
$jjg=checkPostGet('jjg','');
$kg=checkPostGet('kg','');
$bjr=checkPostGet('bjr','');
$prd=checkPostGet('prd','');
$bjr=str_replace(',','',$bjr);

$tahunproduksi=$tahun."-".$bln;
$tt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam');
$jbbt=makeOption($dbname,'setup_blok','kodeorg,jenisbibit');

$prdskrg=$tahun."-".$bln;
$periode11=$periode1;
$periode22=$periode2;

	
	#$bln=$bln-1;
	if($bln<0){
		$prdlalubjr = ($tahun-1)."-".addZero(($bln+12),2);
	}else{
		$prdlalubjr = $tahun."-".addZero($bln,2);
	}
$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
switch ($proses) {
######PREVIEW
    case 'preview':
            
            if($kdKebun2=='')
            {
                if($_SESSION['language']=='EN')
                    echo"Warning: Business unit required";
                else
                    echo"Warning: Unit tidak boleh kosong";     
                exit;
            }
			
            if(($bln=='')or($tahun==''))
            {
                if($_SESSION['language']=='EN')
                    echo"Warning: Period required";
                else
                    echo"Warning: Periode tidak boleh kosong";     
                exit;
            }


		######################################
		############# prepare data ###########
		######################################
        
		#bentuk data blok dari rekap panen periode11
		$kdblok1=array();
		$str="select sum(kgwb) as kgwb, sum(jjg) as jjg, sum(kgwb)/sum(jjg) as bjr, blok, substr(tanggal,1,7) as prd  from ".$dbname.".kebun_spb_detail_vw where "
				. " divisi like '".$kdKebun2."%' and  substr(tanggal,1,7) between '".$periode11."' and '".$periode22."' and posting ='1' group by substr(tanggal,1,7), blok";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
				$kdblok1[$bar['blok']][$bar['prd']]=$bar['blok'];
				$kgwb1[$bar['blok']][$bar['prd']]=$bar['kgwb'];
				$jjg1[$bar['blok']][$bar['prd']]=$bar['jjg'];
				$bjr1[$bar['blok']][$bar['prd']]=$bar['bjr'];
				@$tkgwb[$bar['blok']]+=$bar['kgwb'];
				@$tjjg[$bar['blok']]+=$bar['jjg'];
			}
		
		#Ambil BJR Setup periode lalu
		$str="select kodeorg as blok, bjr, periode from ".$dbname.".kebun_5bjr where "
				. " kodeorg like '".$kdKebun2."%' and  periode = '".$prdlalubjr."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
				@$bjrsetuplalu[$bar['blok']]=$bar['bjr'];
			}
		
		$jlhbln=month_inbetween($periode1,$periode2);
		$col=count($jlhbln);
		
		$stream="<table class=sortable cellspacing=1>";
		$stream.="
			<thead>
				<tr class=rowheader>
					<td align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</td>  
					<td align=center rowspan=2>" . $_SESSION['lang']['divisi'] . "</td>     
					<td align=center rowspan=2>" . $_SESSION['lang']['blok'] . "</td>     
					<td align=center rowspan=2>" . $_SESSION['lang']['tahuntanam'] . "</td>     
					<td align=center rowspan=2>" . $_SESSION['lang']['jenisbibit'] . "</td>     
					<td align=center rowspan=2>" . $_SESSION['lang']['periode'] . "</td>     
					<td align=center rowspan=2>" . $_SESSION['lang']['bjr']." Setup<br>" . $prdlalubjr. "</td>   
					<td align=center colspan=".($col+1).">" . $_SESSION['lang']['periode']."</td>   
					<td align=center rowspan=2>" . $_SESSION['lang']['bjr'] . "<br>" . $prdskrg. "</td>     
					</tr><tr>";
					foreach($jlhbln as $bulan){
						$stream.="<td align=center>" . $bulan. "</td>";
					}
		$stream.="<td align=center>" . $_SESSION['lang']['bjr'] . " " . $_SESSION['lang']['rerata'] . "</td>";
		
		$stream.="
				</tr>
			</thead>
		 <tbody>";

			
		foreach($kdblok1 as $blok => $fblok){
				$no+=1;
				$stream.="<tr class=rowcontent id=row".$no.">
							<td align=center >".$no."</td>
							<td align=center id=divisi".$no.">".substr($blok,0,6)."</td>
							<td align=center hidden id=blok".$no.">".$blok."</td>
							<td align=center>".$optNmOrg[$blok]."</td>
							<td align=center id=tt".$no.">".$tt[$blok]."</td>   
							<td align=left id=jnsbbt".$no.">".$jbbt[$blok]."</td>   
							<td align=left id=prd".$no.">".$tahunproduksi."</td>   
							<td align=right><font ".@$bgs.">".@number_format($bjrsetuplalu[$blok],2)."</font></td>";
				foreach($jlhbln as $bulan => $fbulan){
					$stream.="<td align=right>".@number_format(($bjr1[$blok][$bulan]==0?'':$bjr1[$blok][$bulan]),2)."</td>";

				}
				
				$bjrrata[$blok]=$tkgwb[$blok]/$tjjg[$blok];
					if($bjrrata[$blok]>=@$bjrsetuplalu[$blok]){
						$bjrs[$blok]=$bjrrata[$blok];
					} else if ($bjrsetuplalu[$blok]>$bjrrata[$blok]){
						$bjrs[$blok]=$bjrsetuplalu[$blok];
					} else{
						$bjrs[$blok]=$bjrrata[$blok];
					}
				$stream.=" <td align=right>".@number_format(fixnan($bjrrata[$blok]),2)."</td>";
				$stream.=" <td><input style=width:50px class=myinputtextnumber onkeyup=\"z.numberFormat('bjr".$no."',2)\" onkeypress='return tanpa_kutip(event)' id=bjr".$no." value='".@number_format(fixnan($bjrs[$blok]),2)."'></td>";
				$stream.="  </tr>";
			
		}
		$stream.="<button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['proses']."</button>";
		$stream.="
		 </tbody>
			 </table>";
	echo $stream;
    break;
        

    case'savedata':
        $str="insert into ".$dbname.".kebun_5bjr (`kodeorg`, `kelaspohon`, `bjr`, `tahunproduksi`,`periode`,`updateby`) 
        values ('".$blok."','','".$bjr."','".substr($prd,0,4)."','".$prd."','".$_SESSION['standard']['userid']."')";
        try
        {
            $owlPDO->exec($str); 
        }
        catch (PDOException $e) 
        {
            $str="update  ".$dbname.".kebun_5bjr set bjr='".$bjr."',"
                    . "updateby='".$_SESSION['standard']['userid']."' where kodeorg='".$blok."' and periode='".$prd."' ";
            try
            {
                $owlPDO->exec($str); 
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "\n"; 
                die(); 
            }
        }
    break;
}
?>