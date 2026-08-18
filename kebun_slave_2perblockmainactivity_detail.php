<?php
require_once('master_validation.php');
require_once('lib/zLib.php');


$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$pt       = checkPostGet('pt', '');
$tt       = checkPostGet('tt', '');
$ip       = checkPostGet('ip', '');
$divisi   = checkPostGet('divisi', '');
$prd      = checkPostGet('prd', '');
$tipe     = checkPostGet('tipe', '');
$kolomhide= checkPostGet('kolomhide', '');
$barishide= checkPostGet('barishide', '');
$blok= checkPostGet('blok', '');


$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $tahun."-12";
$periode2 = $prd;

$periodelalu1 = ($tahun-1)."-01";
$periodelalu2 = ($tahun-1)."-".$bulan;
$periodelalusetahun2 = ($tahun-1)."-12";


$rangebln = month_inbetween($periode1,$periode2);

if($pt==''){exit("warning : Kode PT harus di pilih.");}

if($kolomhide=='1'){	
	$style="";
}else{	
	$style="style=display:none";
}


if($style=="style=display:none" and $proses=='getdetail'){
	$colspan="3";
}else{
	$colspan=(count($rangebln)+3);
}

if($proses=='getdetail'){	
	$tab.="<input hidden id=colhide value=3>";
	$tab.="<input hidden id=colunhide value=".(count($rangebln)+3).">";
}

$whtbs=$whhrg="";
if($pt!=''){
	$whtbs=" and a.supplierid in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whhrg=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
}
if($kdorg!=''){
	$whtbs=" and a.supplierid ='".$kdorg."'";
	$whhrg=" and a.kodeorg ='".$kdorg."'";
}
$tab.="<button onclick=kembali(); class=mybutton >" . $_SESSION['lang']['back'] . "</button>";
$tab.="<br>";

$prdton=$prdtontitle=0;
$str = "select sum(kgwb) as kgwb, blok from " . $dbname . ".kebun_spb_vw a  where 1=1 and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' and blok = '".$blok."'"; 
$res = fetchdata($str);
foreach($res as $bar){	
	if($bar['kgwb']>0){		
		$prdtontitle+=($bar['kgwb']/1000);
	}
}





$nmtt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
$nmha=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$blok."'");
$nmpkk=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$blok."'");

$tab.="<table>";

$tab.="
	<tr>
		<td>".$_SESSION['lang']['blok']."</td>
		<td>:</td>
		<td align=left>".$blok."</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['luas']." (Ha)</td>
		<td>:</td>
		<td align=right>".$nmha[$blok]."</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['pokok']." (Pkk)</td>
		<td>:</td>
		<td align=right>".$nmpkk[$blok]."</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['produksi']." (Ton)</td>
		<td>:</td>
		<td align=right>".numb_format($prdtontitle,2)."</td>
	</tr>
	
	
	
	
	";
$tab.="</table>";
  


$stylefont="style=font-weight:normal;color:#A0A0A0";
$stylefontbln="style=font-weight:normal;color:#02BC28";

if($proses!='excel'){	
	$tab.="<table class=sortable cellspacing=1>";
}else{
	$tab.="<table border=1 class=sortable cellspacing=1>";
}
$tab.="
    <thead>
        <tr class=rowheaddet_er title=\"Click untuk show atau hide kolom.\">
            <th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['jenis']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['akun']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['kegiatan']."</th>
			
			";
			
			#Harvesting Cost (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=headdet_pnnlab ".$style." onclick=showhide('pnnlab_det[]','headdet_pnnlab#subdet_pnnlbr','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=headdet_pnnmat ".$style." onclick=showhide('pnnmat_det[]','headdet_pnnmat#subdet_pnnmat','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=headdet_pnntrans ".$style." onclick=showhide('pnntrans_det[]','headdet_pnntrans#subdet_pnntrans','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=headdet_pnnoth ".$style." onclick=showhide('pnnoth_det[]','headdet_pnnoth#subdet_pnnoth','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headdet_pnnttl onclick=showalldetail('costpnn_det[]','pnn_det[]',this.id,'headdet_pnnttl#subdet_pnnttl')>Harvesting Cost (Rp 000)</th>";
			
			#Transport Cost (Rp 000)
			/* $tab.="
			<th align=center colspan=".$colspan." class=trans_det[] name=costtrans_det[] id=headdet_translab ".$style." onclick=showhide('translab_det[]','headdet_translab#subdet_translbr','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=trans_det[] name=costtrans_det[] id=headdet_transmat ".$style." onclick=showhide('transmat_det[]','headdet_transmat#subdet_transmat','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=trans_det[] name=costtrans_det[] id=headdet_transtrans ".$style." onclick=showhide('transtrans_det[]','headdet_transtrans#subdet_transtrans','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=trans_det[] name=costtrans_det[] id=headdet_transoth ".$style." onclick=showhide('transoth_det[]','headdet_transoth#subdet_transoth','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headdet_transttl onclick=showalldetail('costtrans_det[]','trans_det[]',this.id,'headdet_transttl#subdet_transttl')>Transport Cost (Rp 000)</th>"; */

			#Fertilizing Cost (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=ppk_det[] name=costppk_det[] id=headdet_ppklab ".$style." onclick=showhide('ppklab_det[]','headdet_ppklab#subdet_ppklbr','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=ppk_det[] name=costppk_det[] id=headdet_ppkmat ".$style." onclick=showhide('ppkmat_det[]','headdet_ppkmat#subdet_ppkmat','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=ppk_det[] name=costppk_det[] id=headdet_ppktrans ".$style." onclick=showhide('ppktrans_det[]','headdet_ppktrans#subdet_ppktrans','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=ppk_det[] name=costppk_det[] id=headdet_ppkoth ".$style." onclick=showhide('ppkoth_det[]','headdet_ppkoth#subdet_ppkoth','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headdet_ppkttl onclick=showalldetail('costppk_det[]','ppk_det[]',this.id,'headdet_ppkttl#subdet_ppkttl')>Fertilizing Cost (Rp 000)</th>";

			#Maintenance Mature Cost (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=tm_det[] name=costtm_det[] id=headdet_tmlab ".$style." onclick=showhide('tmlab_det[]','headdet_tmlab#subdet_tmlbr','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tm_det[] name=costtm_det[] id=headdet_tmmat ".$style." onclick=showhide('tmmat_det[]','headdet_tmmat#subdet_tmmat','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tm_det[] name=costtm_det[] id=headdet_tmtrans ".$style." onclick=showhide('tmtrans_det[]','headdet_tmtrans#subdet_tmtrans','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tm_det[] name=costtm_det[] id=headdet_tmoth ".$style." onclick=showhide('tmoth_det[]','headdet_tmoth#subdet_tmoth','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headdet_tmttl onclick=showalldetail('costtm_det[]','tm_det[]',this.id,'headdet_tmttl#subdet_tmttl')>Maintenance Mature Cost (Rp 000)</th>";
			
			
			
		$tab.="	
        </tr>
        <tr class=rowheaddet_er title=\"Click untuk show atau hide kolom.\">";
			#Harvesting Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=subdet_pnnlbr ".$style." onclick=showhide('pnnlab_det[]','headdet_pnnlab#subdet_pnnlbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=subdet_pnnmat ".$style." onclick=showhide('pnnmat_det[]','headdet_pnnmat#subdet_pnnmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=subdet_pnntrans ".$style." onclick=showhide('pnntrans_det[]','headdet_pnntrans#subdet_pnntrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=pnn_det[] name=costpnn_det[] id=subdet_pnnoth ".$style." onclick=showhide('pnnoth_det[]','headdet_pnnoth#subdet_pnnoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subdet_pnnttl  onclick=showhide('pnnttl_det[]','headdet_pnnttl#subdet_pnnttl','1')>Total</th>";

			/* #Transport Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=trans_det[]  name=costtrans_det[] id=subdet_translbr ".$style." onclick=showhide('translab_det[]','headdet_translab#subdet_translbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=trans_det[]  name=costtrans_det[] id=subdet_transmat ".$style." onclick=showhide('transmat_det[]','headdet_transmat#subdet_transmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=trans_det[]  name=costtrans_det[] id=subdet_transtrans ".$style." onclick=showhide('transtrans_det[]','headdet_transtrans#subdet_transtrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=trans_det[]  name=costtrans_det[] id=subdet_transoth ".$style." onclick=showhide('transoth_det[]','headdet_transoth#subdet_transoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subdet_transttl  onclick=showhide('transttl_det[]','headdet_transttl#subdet_transttl','1')>Total</th>";
 */
			#Fertilizing Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=ppk_det[]  name=costppk_det[] id=subdet_ppklbr ".$style." onclick=showhide('ppklab_det[]','headdet_ppklab#subdet_ppklbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=ppk_det[]  name=costppk_det[] id=subdet_ppkmat ".$style." onclick=showhide('ppkmat_det[]','headdet_ppkmat#subdet_ppkmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=ppk_det[]  name=costppk_det[] id=subdet_ppktrans ".$style." onclick=showhide('ppktrans_det[]','headdet_ppktrans#subdet_ppktrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=ppk_det[]  name=costppk_det[] id=subdet_ppkoth ".$style." onclick=showhide('ppkoth_det[]','headdet_ppkoth#subdet_ppkoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subdet_ppkttl  onclick=showhide('ppkttl_det[]','headdet_ppkttl#subdet_ppkttl','1')>Total</th>";

			#Maintenance Mature Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=tm_det[]  name=costtm_det[] id=subdet_tmlbr ".$style." onclick=showhide('tmlab_det[]','headdet_tmlab#subdet_tmlbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tm_det[]  name=costtm_det[] id=subdet_tmmat ".$style." onclick=showhide('tmmat_det[]','headdet_tmmat#subdet_tmmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tm_det[]  name=costtm_det[] id=subdet_tmtrans ".$style." onclick=showhide('tmtrans_det[]','headdet_tmtrans#subdet_tmtrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tm_det[]  name=costtm_det[] id=subdet_tmoth ".$style." onclick=showhide('tmoth_det[]','headdet_tmoth#subdet_tmoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subdet_tmttl  onclick=showhide('tmttl_det[]','headdet_tmttl#subdet_tmttl','1')>Total</th>";

			

		$tab.="</tr>
		
        <tr class=rowheader>";
			
		
			#Harvesting Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnlab_det[] class=pnn_det[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnmat_det[] class=pnn_det[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnntrans_det[] class=pnn_det[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnoth_det[] class=pnn_det[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn_det[] class=pnn_det[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnttl_det[] class=pnn_det[] ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			/* #Transport Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=translab_det[] class=trans_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transmat_det[]  class=trans_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transtrans_det[]  class=trans_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transoth_det[]  class=trans_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans_det[] class=trans_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transttl_det[]  class=trans_det[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	
 */
			#Fertilizing Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppklab_det[] class=ppk_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppkmat_det[]  class=ppk_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppktrans_det[]  class=ppk_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppkoth_det[]  class=ppk_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk_det[] class=ppk_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppkttl_det[]  class=ppk_det[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			#Maintenance Mature Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmlab_det[] class=tm_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmmat_det[]  class=tm_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmtrans_det[]  class=tm_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmoth_det[]  class=tm_det[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm_det[] class=tm_det[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmttl_det[]  class=tm_det[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			
    $tab.="</tr>
		
    </thead>
 <tbody>";

$where=$where2=$where_spb=$whereJ=$whtbs="";
if($pt!=''){
	$where=" and substr(a.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where2=" and substr(a.kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where_spb=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$whereJ=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whtbs=" and a.supplierid in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
}
if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
	$whereJ=" and a.kodeorg ='".$kdorg."'";
	$whtbs=" and a.supplierid ='".$kdorg."'";
}

$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';
if($divisi!=''){
	$wh.=" and a.kodeblok like '".$divisi."%'";
	$whB.=" and a.kodeblok like '".$divisi."%'";
	$wh2.=" and a.kodeorg like '".$divisi."%'";
	$wh_spb.=" and a.divisi like '".$divisi."%'";
	$wh_bgt.=" and a.divisi like '".$divisi."%'";
	$wh_bgtrp.=" and a.kodeorg like '".$divisi."%'";
	$whereJ.=" and a.kodeblok like '".$divisi."%'";
}
if($tt!=''){
	$whereJ.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$wh2.=" and a.tahuntanam='".$tt."'";
	$whB.=" and a.thntnm='".$tt."'";
	$wh_spb.=" and a.tahuntanam='".$tt."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
}
if($ip!=''){
	$whereJ.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
	$wh2.=" and a.intiplasma='".$ip."'";
	$whB.=" and a.intiplasma='".$ip."'";
	$wh_spb.=" and a.intiplasma='".$ip."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
}



#### === DARI SINI ====

$akunupahpnn=array('6110101','6110102');
$akuntranspnn=array('6110103','6110104');

$lbrpupuk=array('621010302','621010305','621010308');
$transpupuk=array('621010323','621010324');

$whakun=" and substr(noakun,1,3) in ('611','621')";
$whakunumum=" and noakun like '7%'";

# biaya tahun ini
$byypnnlab=array();
$str = "select a.kodekegiatan,kodeorg,a.kodeblok, sum(jumlah) as jumlah, periode,noakun,kodejurnal 
from " . $dbname . ".keu_jurnaldt_vw a   
where 1=1 ".$whereJ." and periode between '".$periode1."' and  '".$periode2."' ".$whakun." and a.kodeblok='".$blok."'    
group by periode,noakun,kodejurnal,kodekegiatan"; 
$res = fetchdata($str);
foreach($res as $bar){
	$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	#$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['noakun']]=$bar['noakun'];
	
	if($bar['kodekegiatan']==''){
		$bar['kodekegiatan']=$bar['noakun'];
	}
	
	if(substr($bar['noakun'],0,3)=='611'){
		#biaya panen
		if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			#labor
			$byypnnlab[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)=='INV'){
			#material
			$byypnnmat[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
			#transport
			$byypnntrans[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
		}else{
			$byypnnoth[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
		}
	}
	
	
	if(substr($bar['noakun'],0,3)=='621'){
		#TM
		if($bar['noakun']=='6210103'){
			#biaya pupuk
			if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
				#labor
				$byyppklab[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byyppkmat[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
				#transport
				$byyppktrans[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}else{
				$byyppkoth[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}
		}else{
			#biaya pemel
			if(substr($bar['kodejurnal'],0,3)!='INV'){
				#labor
				$byytmlab[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byytmmat[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$byytmtrans[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}else{
				$byytmoth[$bar['kodekegiatan']][$bar['periode']]+=($bar['jumlah']/1000);
			}
		}
	}
}



# biaya tahun lalu
#KHUSUS BYY LAPANGAN
$str = "select a.kodekegiatan,kodeorg,a.kodeblok, sum(jumlah) as jumlah, periode,noakun,kodejurnal 
from " . $dbname . ".keu_jurnaldt_vw a   
where 1=1 ".$whereJ." and periode between '".$periodelalu1."' and  '".$periodelalu2."' ".$whakun." and a.kodeblok='".$blok."'    
group by periode,noakun,kodejurnal,kodekegiatan"; 
$res = fetchdata($str);
foreach($res as $bar){
	$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	#$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['noakun']]=$bar['noakun'];
	
	if($bar['kodekegiatan']==''){
		$bar['kodekegiatan']=$bar['noakun'];
	}
	
	if(substr($bar['noakun'],0,3)=='611'){
		#biaya panen
		if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			#labor
			$byypnnlablalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)=='INV'){
			#material
			$byypnnmatlalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
			#transport
			$byypnntranslalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
		}else{
			$byypnnothlalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
		}
	}
	if(substr($bar['noakun'],0,3)=='621'){
		#TM
		if($bar['noakun']=='6210103'){
			#biaya pupuk
			if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
				#labor
				$byyppklablalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byyppkmatlalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
				#transport
				$byyppktranslalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}else{
				$byyppkothlalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}
		}else{
			#biaya pemel
			if(substr($bar['kodejurnal'],0,3)!='INV'){
				#labor
				$byytmlablalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byytmmatlalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$byytmtranslalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}else{
				$byytmothlalu[$bar['kodekegiatan']]+=($bar['jumlah']/1000);
			}
		}
	}
}


$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";
$whereakun = " and (substr(noakun,1,3) in ('611','621') or noakun like '7%')";

#ini khusus budget kebun
$str=" select kegiatan,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." and kodeorg='".$blok."'";
$res = fetchdata($str);
foreach($res as $bar){
	$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['kegiatan']]=$bar['kegiatan'];
	#$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['noakun']]=$bar['noakun'];
	
	if(substr($bar['noakun'],0,3)=='611'){
		if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
			#LABOUR
			$byypnnlabaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
		}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
			#MATERIAL			
			$byypnnmataop[$bar['kegiatan']]+=($bar['rupiah']/1000);
		}else if($bar['kodebudget']=='VHC'){
			#TRANS
			$byypnntransaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
		}else{
			#OTHER
			$byypnnothaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
		}
	}
	
	if(substr($bar['noakun'],0,3)=='621'){
		if($bar['noakun']=='6210103'){
			#PUPUK
			if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
				#LABOUR
				$byyppklabaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
				#MATERIAL			
				$byyppkmataop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}else if($bar['kodebudget']=='VHC'){
				#TRANS
				$byyppktransaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}else {
				#OTHER
				$byyppkothaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}
		}else{			
			if((substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK')){
				#LABOUR
				$byytmlabaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}else if((substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL')){
				#MATERIAL			
				$byytmmataop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}else if($bar['kodebudget']=='VHC'){
				#TRANS
				$byytmtransaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}else{
				#OTHER
				$byytmothaop[$bar['kegiatan']]+=($bar['rupiah']/1000);
			}
		}
	}
}

#number format
$nf2=2;
$nf0=0;

#number format
if($barishide=='1'){
	$stylerow="";
}else{	
	$stylerow="style=display:none";
}	

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan');
$no=0;$nodiv=0;$gtrluas=0;
$tdluas=$teluas=$tbcluas=$tdcluas=array();
foreach($kodeblok as $estate => $valdiv){
	$est=0;
	foreach($valdiv as $div => $valkodeblok){
		$row=0;$nodiv+=1;
		foreach($valkodeblok as $blok){
			$row+=1;$est+=1;$no+=1;
			$nmtt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
			$nmha=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$blok."'");
			$nmpkk=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$blok."'");
			
			$title="";
			$title.="\nKegiatan : ".$blok." - ".$nmkeg[$blok]."";
			$title.="\nProduksi : ".numb_format($prdtontitle,2)." Ton";
			
			$tab.="<tr class=rowcontent ".$stylerow." id=row_det_".$no." name=".$estate."[] onclick=getmark(this.id); title=\"Click untuk memberi tanda.".$title."\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$nmakun[$estate]."</td>";
			$tab.="<td>".$nmakun[$div]."</td>";
			$tab.="<td>".$nmkeg[$blok]."</td>";
			
				
			#ttl divisi kanan	
			$prdknytdtydiv[$div]+=$prdytdty[$blok];	
			$prdknlaludiv[$div]+=$prdtonlalu[$blok];	
			$prdknaopdiv[$div]+=$prdtonaop[$blok];	
				
			#ttl estate kanan	
			$prdknytdtyest[$estate]+=$prdytdty[$blok];	
			$prdknlaluest[$estate]+=$prdtonlalu[$blok];	
			$prdknaopest[$estate]+=$prdtonaop[$blok];	
				
			#grand total	
			$prdknytdtygt+=$prdytdty[$blok];	
			$prdknlalugt+=$prdtonlalu[$blok];	
			$prdknaopgt+=$prdtonaop[$blok];	
			# === end Production (Ton) ===	
			
			# === Harvesting Cost (Rp Mn)==>lab ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnnlab_det[] class=pnn_det[] ".$style.">".numb_format($byypnnlab[$blok][$bln],$nf0)."</td>";
				$pnnlabytdty[$blok]+=$byypnnlab[$blok][$bln];
				$pnnlabttldiv[$div][$bln]+=$byypnnlab[$blok][$bln];
				$pnnlabttlest[$estate][$bln]+=$byypnnlab[$blok][$bln];
				$pnnlabgt[$bln]+=$byypnnlab[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($pnnlabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnnlablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnnlabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnlabknytdtydiv[$div]+=$pnnlabytdty[$blok];	
			$pnnlabknlaludiv[$div]+=$byypnnlablalu[$blok];	
			$pnnlabknaopdiv[$div]+=$byypnnlabaop[$blok];	
			#ttl estate kanan	
			$pnnlabknytdtyest[$estate]+=$pnnlabytdty[$blok];	
			$pnnlabknlaluest[$estate]+=$byypnnlablalu[$blok];	
			$pnnlabknaopest[$estate]+=$byypnnlabaop[$blok];	
			#grand total	
			$pnnlabknytdtygt+=$pnnlabytdty[$blok];	
			$pnnlabknlalugt+=$byypnnlablalu[$blok];	
			$pnnlabknaopgt+=$byypnnlabaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>lab ===	
			# === Harvesting Cost (Rp Mn)==>mat ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnnmat_det[] class=pnn_det[] ".$style.">".numb_format($byypnnmat[$blok][$bln],$nf0)."</td>";
				$pnnmatytdty[$blok]+=$byypnnmat[$blok][$bln];
				$pnnmatttldiv[$div][$bln]+=$byypnnmat[$blok][$bln];
				$pnnmatttlest[$estate][$bln]+=$byypnnmat[$blok][$bln];
				$pnnmatgt[$bln]+=$byypnnmat[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($pnnmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnnmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnnmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnmatknytdtydiv[$div]+=$pnnmatytdty[$blok];	
			$pnnmatknlaludiv[$div]+=$byypnnmatlalu[$blok];	
			$pnnmatknaopdiv[$div]+=$byypnnmataop[$blok];	
			#ttl estate kanan	
			$pnnmatknytdtyest[$estate]+=$pnnmatytdty[$blok];	
			$pnnmatknlaluest[$estate]+=$byypnnmatlalu[$blok];	
			$pnnmatknaopest[$estate]+=$byypnnmataop[$blok];	
			#grand total	
			$pnnmatknytdtygt+=$pnnmatytdty[$blok];	
			$pnnmatknlalugt+=$byypnnmatlalu[$blok];	
			$pnnmatknaopgt+=$byypnnmataop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>mat ===	
			# === Harvesting Cost (Rp Mn)==>trans ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnntrans_det[] class=pnn_det[] ".$style.">".numb_format($byypnntrans[$blok][$bln],$nf0)."</td>";
				$pnntransytdty[$blok]+=$byypnntrans[$blok][$bln];
				$pnntransttldiv[$div][$bln]+=$byypnntrans[$blok][$bln];
				$pnntransttlest[$estate][$bln]+=$byypnntrans[$blok][$bln];
				$pnntransgt[$bln]+=$byypnntrans[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($pnntransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnntranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnntransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnntransknytdtydiv[$div]+=$pnntransytdty[$blok];	
			$pnntransknlaludiv[$div]+=$byypnntranslalu[$blok];	
			$pnntransknaopdiv[$div]+=$byypnntransaop[$blok];	
			#ttl estate kanan	
			$pnntransknytdtyest[$estate]+=$pnntransytdty[$blok];	
			$pnntransknlaluest[$estate]+=$byypnntranslalu[$blok];	
			$pnntransknaopest[$estate]+=$byypnntransaop[$blok];	
			#grand total	
			$pnntransknytdtygt+=$pnntransytdty[$blok];	
			$pnntransknlalugt+=$byypnntranslalu[$blok];	
			$pnntransknaopgt+=$byypnntransaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>trans ===	
			# === Harvesting Cost (Rp Mn)==>oth ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnnoth_det[] class=pnn_det[] ".$style.">".numb_format($byypnnoth[$blok][$bln],$nf0)."</td>";
				$pnnothytdty[$blok]+=$byypnnoth[$blok][$bln];
				$pnnothttldiv[$div][$bln]+=$byypnnoth[$blok][$bln];
				$pnnothttlest[$estate][$bln]+=$byypnnoth[$blok][$bln];
				$pnnothgt[$bln]+=$byypnnoth[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($pnnothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnnothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn_det[] class=pnn_det[] ".$style.">".numb_format($byypnnothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnothknytdtydiv[$div]+=$pnnothytdty[$blok];	
			$pnnothknlaludiv[$div]+=$byypnnothlalu[$blok];	
			$pnnothknaopdiv[$div]+=$byypnnothaop[$blok];	
			#ttl estate kanan	
			$pnnothknytdtyest[$estate]+=$pnnothytdty[$blok];	
			$pnnothknlaluest[$estate]+=$byypnnothlalu[$blok];	
			$pnnothknaopest[$estate]+=$byypnnothaop[$blok];	
			#grand total	
			$pnnothknytdtygt+=$pnnothytdty[$blok];	
			$pnnothknlalugt+=$byypnnothlalu[$blok];	
			$pnnothknaopgt+=$byypnnothaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>oth ===	
			
			# === Harvesting Cost (Rp Mn)==>ttl ===	
			$byypnnttl=$byypnnttllalu=$byypnnttlaop[$blok]=array();
			foreach($rangebln as $bln){	
				$byypnnttl[$blok][$bln]=$byypnnlab[$blok][$bln]+$byypnnmat[$blok][$bln]+$byypnntrans[$blok][$bln]+$byypnnoth[$blok][$bln];
				
				
				$tab.="<td align=right name=pnnttl_det[] class=pnn_det[] ".$style.">".numb_format($byypnnttl[$blok][$bln],$nf0)."</td>";
				$pnnttlytdty[$blok]+=$byypnnttl[$blok][$bln];
				$pnnttlttldiv[$div][$bln]+=$byypnnttl[$blok][$bln];
				$pnnttlttlest[$estate][$bln]+=$byypnnttl[$blok][$bln];
				$pnnttlgt[$bln]+=$byypnnttl[$blok][$bln];
			}	
			$byypnnttllalu[$blok]=$byypnnlablalu[$blok]+$byypnnmatlalu[$blok]+$byypnntranslalu[$blok]+$byypnnothlalu[$blok];
			$byypnnttlaop[$blok]=$byypnnlabaop[$blok]+$byypnnmataop[$blok]+$byypnntransaop[$blok]+$byypnnothaop[$blok];
			
			$tab.="<td align=right >".numb_format($pnnttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byypnnttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byypnnttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnttlknytdtydiv[$div]+=$pnnttlytdty[$blok];	
			$pnnttlknlaludiv[$div]+=$byypnnttllalu[$blok];	
			$pnnttlknaopdiv[$div]+=$byypnnttlaop[$blok];	
			#ttl estate kanan	
			$pnnttlknytdtyest[$estate]+=$pnnttlytdty[$blok];	
			$pnnttlknlaluest[$estate]+=$byypnnttllalu[$blok];	
			$pnnttlknaopest[$estate]+=$byypnnttlaop[$blok];	
			#grand total	
			$pnnttlknytdtygt+=$pnnttlytdty[$blok];	
			$pnnttlknlalugt+=$byypnnttllalu[$blok];	
			$pnnttlknaopgt+=$byypnnttlaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>ttl ===	
			
				
			# === Fertilizing Cost (Rp Mn)==>lab ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppklab_det[] class=ppk_det[] ".$style.">".numb_format($byyppklab[$blok][$bln],$nf0)."</td>";
				$ppklabytdty[$blok]+=$byyppklab[$blok][$bln];
				$ppklabttldiv[$div][$bln]+=$byyppklab[$blok][$bln];
				$ppklabttlest[$estate][$bln]+=$byyppklab[$blok][$bln];
				$ppklabgt[$bln]+=$byyppklab[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($ppklabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppklablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppklabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppklabknytdtydiv[$div]+=$ppklabytdty[$blok];	
			$ppklabknlaludiv[$div]+=$byyppklablalu[$blok];	
			$ppklabknaopdiv[$div]+=$byyppklabaop[$blok];	
			#ttl estate kanan	
			$ppklabknytdtyest[$estate]+=$ppklabytdty[$blok];	
			$ppklabknlaluest[$estate]+=$byyppklablalu[$blok];	
			$ppklabknaopest[$estate]+=$byyppklabaop[$blok];	
			#grand total	
			$ppklabknytdtygt+=$ppklabytdty[$blok];	
			$ppklabknlalugt+=$byyppklablalu[$blok];	
			$ppklabknaopgt+=$byyppklabaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>lab ===	
			# === Fertilizing Cost (Rp Mn)==>mat ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppkmat_det[] class=ppk_det[] ".$style.">".numb_format($byyppkmat[$blok][$bln],$nf0)."</td>";
				$ppkmatytdty[$blok]+=$byyppkmat[$blok][$bln];
				$ppkmatttldiv[$div][$bln]+=$byyppkmat[$blok][$bln];
				$ppkmatttlest[$estate][$bln]+=$byyppkmat[$blok][$bln];
				$ppkmatgt[$bln]+=$byyppkmat[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($ppkmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppkmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppkmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppkmatknytdtydiv[$div]+=$ppkmatytdty[$blok];	
			$ppkmatknlaludiv[$div]+=$byyppkmatlalu[$blok];	
			$ppkmatknaopdiv[$div]+=$byyppkmataop[$blok];	
			#ttl estate kanan	
			$ppkmatknytdtyest[$estate]+=$ppkmatytdty[$blok];	
			$ppkmatknlaluest[$estate]+=$byyppkmatlalu[$blok];	
			$ppkmatknaopest[$estate]+=$byyppkmataop[$blok];	
			#grand total	
			$ppkmatknytdtygt+=$ppkmatytdty[$blok];	
			$ppkmatknlalugt+=$byyppkmatlalu[$blok];	
			$ppkmatknaopgt+=$byyppkmataop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>mat ===	
			# === Fertilizing Cost (Rp Mn)==>trans ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppktrans_det[] class=ppk_det[] ".$style.">".numb_format($byyppktrans[$blok][$bln],$nf0)."</td>";
				$ppktransytdty[$blok]+=$byyppktrans[$blok][$bln];
				$ppktransttldiv[$div][$bln]+=$byyppktrans[$blok][$bln];
				$ppktransttlest[$estate][$bln]+=$byyppktrans[$blok][$bln];
				$ppktransgt[$bln]+=$byyppktrans[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($ppktransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppktranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppktransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppktransknytdtydiv[$div]+=$ppktransytdty[$blok];	
			$ppktransknlaludiv[$div]+=$byyppktranslalu[$blok];	
			$ppktransknaopdiv[$div]+=$byyppktransaop[$blok];	
			#ttl estate kanan	
			$ppktransknytdtyest[$estate]+=$ppktransytdty[$blok];	
			$ppktransknlaluest[$estate]+=$byyppktranslalu[$blok];	
			$ppktransknaopest[$estate]+=$byyppktransaop[$blok];	
			#grand total	
			$ppktransknytdtygt+=$ppktransytdty[$blok];	
			$ppktransknlalugt+=$byyppktranslalu[$blok];	
			$ppktransknaopgt+=$byyppktransaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>trans ===	
			# === Fertilizing Cost (Rp Mn)==>oth ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppkoth_det[] class=ppk_det[] ".$style.">".numb_format($byyppkoth[$blok][$bln],$nf0)."</td>";
				$ppkothytdty[$blok]+=$byyppkoth[$blok][$bln];
				$ppkothttldiv[$div][$bln]+=$byyppkoth[$blok][$bln];
				$ppkothttlest[$estate][$bln]+=$byyppkoth[$blok][$bln];
				$ppkothgt[$bln]+=$byyppkoth[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($ppkothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppkothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk_det[] class=ppk_det[] ".$style.">".numb_format($byyppkothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppkothknytdtydiv[$div]+=$ppkothytdty[$blok];	
			$ppkothknlaludiv[$div]+=$byyppkothlalu[$blok];	
			$ppkothknaopdiv[$div]+=$byyppkothaop[$blok];	
			#ttl estate kanan	
			$ppkothknytdtyest[$estate]+=$ppkothytdty[$blok];	
			$ppkothknlaluest[$estate]+=$byyppkothlalu[$blok];	
			$ppkothknaopest[$estate]+=$byyppkothaop[$blok];	
			#grand total	
			$ppkothknytdtygt+=$ppkothytdty[$blok];	
			$ppkothknlalugt+=$byyppkothlalu[$blok];	
			$ppkothknaopgt+=$byyppkothaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>oth ===	
			
				
			# === Fertilizing Cost (Rp Mn)==>ttl ===	
			foreach($rangebln as $bln){	
				$byyppkttl[$blok][$bln]=$byyppklab[$blok][$bln]+$byyppkmat[$blok][$bln]+$byyppktrans[$blok][$bln]+$byyppkoth[$blok][$bln];
				
				$tab.="<td align=right name=ppkttl_det[] class=ppk_det[] ".$style.">".numb_format($byyppkttl[$blok][$bln],$nf0)."</td>";
				$ppkttlytdty[$blok]+=$byyppkttl[$blok][$bln];
				$ppkttlttldiv[$div][$bln]+=$byyppkttl[$blok][$bln];
				$ppkttlttlest[$estate][$bln]+=$byyppkttl[$blok][$bln];
				$ppkttlgt[$bln]+=$byyppkttl[$blok][$bln];
			}	
			$byyppkttllalu[$blok]  =$byyppklablalu[$blok]+$byyppkmatlalu[$blok]+$byyppktranslalu[$blok]+$byyppkothlalu[$blok];
			
			$byyppkttlaop[$blok]   =$byyppklabaop[$blok]+$byyppkmataop[$blok]+$byyppktransaop[$blok]+$byyppkothaop[$blok];
			$tab.="<td align=right >".numb_format($ppkttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyppkttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyppkttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppkttlknytdtydiv[$div]+=$ppkttlytdty[$blok];	
			$ppkttlknlaludiv[$div]+=$byyppkttllalu[$blok];	
			$ppkttlknaopdiv[$div]+=$byyppkttlaop[$blok];	
			#ttl estate kanan	
			$ppkttlknytdtyest[$estate]+=$ppkttlytdty[$blok];	
			$ppkttlknlaluest[$estate]+=$byyppkttllalu[$blok];	
			$ppkttlknaopest[$estate]+=$byyppkttlaop[$blok];	
			#grand total	
			$ppkttlknytdtygt+=$ppkttlytdty[$blok];	
			$ppkttlknlalugt+=$byyppkttllalu[$blok];	
			$ppkttlknaopgt+=$byyppkttlaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>ttl ===	


			
			
			# === Maintenance Mature Cost (Rp Mn)==>lab ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmlab_det[] class=tm_det[] ".$style.">".numb_format($byytmlab[$blok][$bln],$nf0)."</td>";
				$tmlabytdty[$blok]+=$byytmlab[$blok][$bln];
				$tmlabttldiv[$div][$bln]+=$byytmlab[$blok][$bln];
				$tmlabttlest[$estate][$bln]+=$byytmlab[$blok][$bln];
				$tmlabgt[$bln]+=$byytmlab[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($tmlabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmlablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmlabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmlabknytdtydiv[$div]+=$tmlabytdty[$blok];	
			$tmlabknlaludiv[$div]+=$byytmlablalu[$blok];	
			$tmlabknaopdiv[$div]+=$byytmlabaop[$blok];	
			#ttl estate kanan	
			$tmlabknytdtyest[$estate]+=$tmlabytdty[$blok];	
			$tmlabknlaluest[$estate]+=$byytmlablalu[$blok];	
			$tmlabknaopest[$estate]+=$byytmlabaop[$blok];	
			#grand total	
			$tmlabknytdtygt+=$tmlabytdty[$blok];	
			$tmlabknlalugt+=$byytmlablalu[$blok];	
			$tmlabknaopgt+=$byytmlabaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>lab ===	
			# === Maintenance Mature Cost (Rp Mn)==>mat ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmmat_det[] class=tm_det[] ".$style.">".numb_format($byytmmat[$blok][$bln],$nf0)."</td>";
				$tmmatytdty[$blok]+=$byytmmat[$blok][$bln];
				$tmmatttldiv[$div][$bln]+=$byytmmat[$blok][$bln];
				$tmmatttlest[$estate][$bln]+=$byytmmat[$blok][$bln];
				$tmmatgt[$bln]+=$byytmmat[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($tmmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmmatknytdtydiv[$div]+=$tmmatytdty[$blok];	
			$tmmatknlaludiv[$div]+=$byytmmatlalu[$blok];	
			$tmmatknaopdiv[$div]+=$byytmmataop[$blok];	
			#ttl estate kanan	
			$tmmatknytdtyest[$estate]+=$tmmatytdty[$blok];	
			$tmmatknlaluest[$estate]+=$byytmmatlalu[$blok];	
			$tmmatknaopest[$estate]+=$byytmmataop[$blok];	
			#grand total	
			$tmmatknytdtygt+=$tmmatytdty[$blok];	
			$tmmatknlalugt+=$byytmmatlalu[$blok];	
			$tmmatknaopgt+=$byytmmataop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>mat ===	
			
			# === Maintenance Mature Cost (Rp Mn)==>trans ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmtrans_det[] class=tm_det[] ".$style.">".numb_format($byytmtrans[$blok][$bln],$nf0)."</td>";
				$tmtransytdty[$blok]+=$byytmtrans[$blok][$bln];
				$tmtransttldiv[$div][$bln]+=$byytmtrans[$blok][$bln];
				$tmtransttlest[$estate][$bln]+=$byytmtrans[$blok][$bln];
				$tmtransgt[$bln]+=$byytmtrans[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($tmtransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmtranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmtransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmtransknytdtydiv[$div]+=$tmtransytdty[$blok];	
			$tmtransknlaludiv[$div]+=$byytmtranslalu[$blok];	
			$tmtransknaopdiv[$div]+=$byytmtransaop[$blok];	
			#ttl estate kanan	
			$tmtransknytdtyest[$estate]+=$tmtransytdty[$blok];	
			$tmtransknlaluest[$estate]+=$byytmtranslalu[$blok];	
			$tmtransknaopest[$estate]+=$byytmtransaop[$blok];	
			#grand total	
			$tmtransknytdtygt+=$tmtransytdty[$blok];	
			$tmtransknlalugt+=$byytmtranslalu[$blok];	
			$tmtransknaopgt+=$byytmtransaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>trans ===	
			# === Maintenance Mature Cost (Rp Mn)==>oth ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmoth_det[] class=tm_det[] ".$style.">".numb_format($byytmoth[$blok][$bln],$nf0)."</td>";
				$tmothytdty[$blok]+=$byytmoth[$blok][$bln];
				$tmothttldiv[$div][$bln]+=$byytmoth[$blok][$bln];
				$tmothttlest[$estate][$bln]+=$byytmoth[$blok][$bln];
				$tmothgt[$bln]+=$byytmoth[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($tmothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm_det[] class=tm_det[] ".$style.">".numb_format($byytmothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmothknytdtydiv[$div]+=$tmothytdty[$blok];	
			$tmothknlaludiv[$div]+=$byytmothlalu[$blok];	
			$tmothknaopdiv[$div]+=$byytmothaop[$blok];	
			#ttl estate kanan	
			$tmothknytdtyest[$estate]+=$tmothytdty[$blok];	
			$tmothknlaluest[$estate]+=$byytmothlalu[$blok];	
			$tmothknaopest[$estate]+=$byytmothaop[$blok];	
			#grand total	
			$tmothknytdtygt+=$tmothytdty[$blok];	
			$tmothknlalugt+=$byytmothlalu[$blok];	
			$tmothknaopgt+=$byytmothaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>oth ===	
			# === Maintenance Mature Cost (Rp Mn)==>ttl ===	
			foreach($rangebln as $bln){
				$byytmttl[$blok][$bln]=	$byytmlab[$blok][$bln]+$byytmmat[$blok][$bln]+$byytmtrans[$blok][$bln]+$byytmoth[$blok][$bln];

				$tab.="<td align=right name=tmttl_det[] class=tm_det[] ".$style.">".numb_format($byytmttl[$blok][$bln],$nf0)."</td>";
				$tmttlytdty[$blok]+=$byytmttl[$blok][$bln];
				$tmttlttldiv[$div][$bln]+=$byytmttl[$blok][$bln];
				$tmttlttlest[$estate][$bln]+=$byytmttl[$blok][$bln];
				$tmttlgt[$bln]+=$byytmttl[$blok][$bln];
			}	
			$byytmttllalu[$blok]=	$byytmlablalu[$blok]+$byytmmatlalu[$blok]+$byytmtranslalu[$blok]+$byytmothlalu[$blok];
			$byytmttlaop[$blok]=	$byytmlabaop[$blok]+$byytmmataop[$blok]+$byytmtransaop[$blok]+$byytmothaop[$blok];
			$tab.="<td align=right >".numb_format($tmttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytmttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytmttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmttlknytdtydiv[$div]+=$tmttlytdty[$blok];	
			$tmttlknlaludiv[$div]+=$byytmttllalu[$blok];	
			$tmttlknaopdiv[$div]+=$byytmttlaop[$blok];	
			#ttl estate kanan	
			$tmttlknytdtyest[$estate]+=$tmttlytdty[$blok];	
			$tmttlknlaluest[$estate]+=$byytmttllalu[$blok];	
			$tmttlknaopest[$estate]+=$byytmttlaop[$blok];	
			#grand total	
			$tmttlknytdtygt+=$tmttlytdty[$blok];	
			$tmttlknlalugt+=$byytmttllalu[$blok];	
			$tmttlknaopgt+=$byytmttlaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>ttl ===	

			
			$awal=($no-$row)+1;
			$awalest=($no-$est)+1;
		}
		# TOTAL PER DIVISI
		$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#E7FCE4; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awal."','".$no."','div','det')>";
		$tab.="<td align=center>".$nodiv."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=left colspan=2>Total ".$nmakun[$div]."</td>";
			
	
		# === Harvesting Cost (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnlab_det[] class=pnn_det[] ".$style.">".numb_format($pnnlabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>lab divisi ===	
		# === Harvesting Cost (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnmat_det[] class=pnn_det[] ".$style.">".numb_format($pnnmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>mat divisi ===	
		# === Harvesting Cost (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnntrans_det[] class=pnn_det[] ".$style.">".numb_format($pnntransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>trans divisi ===	
		# === Harvesting Cost (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnoth_det[] class=pnn_det[] ".$style.">".numb_format($pnnothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>oth divisi ===	
		
		# === Harvesting Cost (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnttl_det[] class=pnn_det[] ".$style.">".numb_format($pnnttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($pnnttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($pnnttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($pnnttlknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>ttl divisi ===	
		
			
		# === Fertilizing Cost (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppklab_det[] class=ppk_det[] ".$style.">".numb_format($ppklabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>lab divisi ===	
		# === Fertilizing Cost (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppkmat_det[] class=ppk_det[] ".$style.">".numb_format($ppkmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>mat divisi ===	
		# === Fertilizing Cost (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppktrans_det[] class=ppk_det[] ".$style.">".numb_format($ppktransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>trans divisi ===	
		# === Fertilizing Cost (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppkoth_det[] class=ppk_det[] ".$style.">".numb_format($ppkothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>oth divisi ===	
		
			
		# === Fertilizing Cost (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppkttl_det[] class=ppk_det[] ".$style.">".numb_format($ppkttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right>".numb_format($ppkttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right>".numb_format($ppkttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right>".numb_format($ppkttlknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>ttl divisi ===	


		# === Maintenance Mature Cost (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmlab_det[] class=tm_det[] ".$style.">".numb_format($tmlabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>lab divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmmat_det[] class=tm_det[] ".$style.">".numb_format($tmmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>mat divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmtrans_det[] class=tm_det[] ".$style.">".numb_format($tmtransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>trans divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmoth_det[] class=tm_det[] ".$style.">".numb_format($tmothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>oth divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmttl_det[] class=tm_det[] ".$style.">".numb_format($tmttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($tmttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tmttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tmttlknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>ttl divisi ===	


		$tab.="</tr>";
	}
	$nodiv+=1;
	# TOTAL ESTATE
	$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awalest."','".$no."','est','det')>";
	$tab.="<td align=center>".$nodiv."</td>";
	$tab.="<td align=left colspan=3>Total ".$nmakun[$estate]."</td>";
	
	

	# === Harvesting Cost (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnlab_det[] class=pnn_det[] ".$style.">".numb_format($pnnlabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style." >".numb_format($pnnlabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>lab estate ===	
	# === Harvesting Cost (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnmat_det[] class=pnn_det[] ".$style.">".numb_format($pnnmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style." >".numb_format($pnnmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>mat estate ===	
	# === Harvesting Cost (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnntrans_det[] class=pnn_det[] ".$style.">".numb_format($pnntransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style." >".numb_format($pnntransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>trans estate ===	
	# === Harvesting Cost (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnoth_det[] class=pnn_det[] ".$style.">".numb_format($pnnothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style." >".numb_format($pnnothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>oth estate ===	
	# === Harvesting Cost (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnttl_det[] class=pnn_det[] ".$style.">".numb_format($pnnttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($pnnttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($pnnttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($pnnttlknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>ttl estate ===	
	
		
	# === Fertilizing Cost (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppklab_det[] class=ppk_det[] ".$style.">".numb_format($ppklabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style." >".numb_format($ppklabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>lab estate ===	
	# === Fertilizing Cost (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppkmat_det[] class=ppk_det[] ".$style.">".numb_format($ppkmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style." >".numb_format($ppkmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>mat estate ===	
	# === Fertilizing Cost (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppktrans_det[] class=ppk_det[] ".$style.">".numb_format($ppktransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style." >".numb_format($ppktransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>trans estate ===	
	# === Fertilizing Cost (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppkoth_det[] class=ppk_det[] ".$style.">".numb_format($ppkothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style." >".numb_format($ppkothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>oth estate ===	
		
	# === Fertilizing Cost (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppkttl_det[] class=ppk_det[] ".$style.">".numb_format($ppkttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($ppkttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ppkttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ppkttlknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>ttl estate ===	

	
	# === Maintenance Mature Cost (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmlab_det[] class=tm_det[] ".$style.">".numb_format($tmlabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style." >".numb_format($tmlabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>lab estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmmat_det[] class=tm_det[] ".$style.">".numb_format($tmmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style." >".numb_format($tmmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>mat estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmtrans_det[] class=tm_det[] ".$style.">".numb_format($tmtransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style." >".numb_format($tmtransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>trans estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmoth_det[] class=tm_det[] ".$style.">".numb_format($tmothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style." >".numb_format($tmothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>oth estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmttl_det[] class=tm_det[] ".$style.">".numb_format($tmttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($tmttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tmttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tmttlknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>ttl estate ===	

	

	$tab.="</tr>";
}
$nodiv+=1;
# GRAND TOTAL
$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('1','".$no."','est','det')>";
$tab.="<td align=left colspan=4>Grand Total</td>";


# === Harvesting Cost (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnlab_det[] class=pnn_det[] ".$style.">".numb_format($pnnlabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnlabknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>lab gt ===	
# === Harvesting Cost (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnmat_det[] class=pnn_det[] ".$style.">".numb_format($pnnmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnmatknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>mat gt ===	
# === Harvesting Cost (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnntrans_det[] class=pnn_det[] ".$style.">".numb_format($pnntransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnntransknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>trans gt ===	
# === Harvesting Cost (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnoth_det[] class=pnn_det[] ".$style.">".numb_format($pnnothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn_det[] class=pnn_det[]  ".$style.">".numb_format($pnnothknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>oth gt ===	
# === Harvesting Cost (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnttl_det[] class=pnn_det[] ".$style.">".numb_format($pnnttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($pnnttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($pnnttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($pnnttlknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>ttl gt ===	
	
	
# === Fertilizing Cost (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppklab_det[] class=ppk_det[] ".$style.">".numb_format($ppklabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppklabknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>lab gt ===	
# === Fertilizing Cost (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppkmat_det[] class=ppk_det[] ".$style.">".numb_format($ppkmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkmatknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>mat gt ===	
# === Fertilizing Cost (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppktrans_det[] class=ppk_det[] ".$style.">".numb_format($ppktransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppktransknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>trans gt ===	
# === Fertilizing Cost (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppkoth_det[] class=ppk_det[] ".$style.">".numb_format($ppkothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk_det[] class=ppk_det[]  ".$style.">".numb_format($ppkothknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>oth gt ===	

	
# === Fertilizing Cost (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppkttl_det[] class=ppk_det[] ".$style.">".numb_format($ppkttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($ppkttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($ppkttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($ppkttlknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>ttl gt ===	


# === Maintenance Mature Cost (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmlab_det[] class=tm_det[] ".$style.">".numb_format($tmlabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmlabknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>lab gt ===	
# === Maintenance Mature Cost (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmmat_det[] class=tm_det[] ".$style.">".numb_format($tmmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmmatknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>mat gt ===	
# === Maintenance Mature Cost (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmtrans_det[] class=tm_det[] ".$style.">".numb_format($tmtransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmtransknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>trans gt ===	
# === Maintenance Mature Cost (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmoth_det[] class=tm_det[] ".$style.">".numb_format($tmothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm_det[] class=tm_det[]  ".$style.">".numb_format($tmothknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>oth gt ===	
# === Maintenance Mature Cost (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmttl_det[] class=tm_det[] ".$style.">".numb_format($tmttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($tmttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tmttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tmttlknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>ttl gt ===	
	

$tab.="</tr>";
$tab.="</tbody></table>";

switch ($proses) {
######PREVIEW
    case 'getdetail':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
        $nop_ = "trend_lmto_per_block_main_activity";
        if (strlen($tab) > 0) {
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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
	break;
}

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}


?>