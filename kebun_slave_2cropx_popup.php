<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$method         = checkPostGet('method', '');
$sumber         = checkPostGet('sumber', '');
$prd            = checkPostGet('periode', '');
$tipe            = checkPostGet('tipe', '');
$tt            = checkPostGet('tt', '');

$tmpPeriod = explode('-',$prd);
$year = $tmpPeriod[0];
$month = $tmpPeriod[1];

$periode1=$year."-01";
$periode2=$prd;

switch($method){
	case'mengumpul':
		switch($tipe){
			case'real':
				$tab="";
				$tab="<table class=sortable cellspacing=1 cellpadding=5>";
				$tab.= "<thead style='text-align:center'>";
				$tab.= "<tr class=rowheader>";
				$tab.= "<th>No</th>";
				$tab.= "<th>".$_SESSION['lang']['noakun']."</th>";
				$tab.= "<th>".$_SESSION['lang']['akun']."</th>";
				$tab.= "<th>BI</th>";
				$tab.= "<th>SD BI</th>";
				$tab .= "</tr>";
				$tab.="</thead>";
				$tab.="<tbody>";
				
				$wh="";
				if($tt!='' and $tt!='nonblok'){
					$wh=" and tahuntanam='".$tt."'";
				}
				if($tt=='nonblok'){
					$wh=" and kodeblok=''";
				}
				if(strlen($sumber)<=4){
					$whr="and a.kodeorg like '".$sumber."%'";
				}else{
					$whr="and kodeblok like '".$sumber."%'";
				}
				
				$str = "select noakun,sum(jumlah) as jumlah, kodeblok,periode,a.kodeorg  
				from " . $dbname . ".keu_jurnaldt_vw a left join " . $dbname . ".setup_blok b on a.kodeblok=b.kodeorg 
				where 1=1 and substr(noakun,1,3) in ('611') ".$whr." ".$wh." and 
				periode between '".$periode1."' and  '".$periode2."' 
				group by periode,kodeblok,noakun"; 
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					@$data[$bar['noakun']]=$bar['noakun'];
					if($bar['periode']==$prd){
						@$jlhbi[$bar['noakun']]+=$bar['jumlah'];
					}
					@$jlhsbi[$bar['noakun']]+=$bar['jumlah'];
				}
				
				$no=0;
				if(count($data)==0){
					exit("Warning : Data Kosong !!!");
				}
				foreach($data as $akun){
					$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$akun."'");
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td align=center>".$akun."</td>";
					$tab.="<td align=left>".$nmakun[$akun]."</td>";
					$tab.="<td align=right>".@number_format($jlhbi[$akun])."</td>";
					$tab.="<td align=right>".@number_format($jlhsbi[$akun])."</td>";
					
					@$tjlhbi+=$jlhbi[$akun];
					@$tjlhsbi+=$jlhsbi[$akun];
				}
				
				$tab.="</tr>";
				$tab.="<tr class=rowcontent style=font-weight:bold>";
				$tab.="<td align=center colspan=3>Total</td>";
				$tab.="<td align=right>".@number_format($tjlhbi)."</td>";
				$tab.="<td align=right>".@number_format($tjlhsbi)."</td>";
				
					
				$tab.="</fieldset>";
				
				echo $tab;
			break;
			case'bgt':
				$tab="";
				$tab="<table class=sortable cellspacing=1 cellpadding=5>";
				$tab.= "<thead style='text-align:center'>";
				$tab.= "<tr class=rowheader>";
				$tab.= "<th>No</th>";
				$tab.= "<th>".$_SESSION['lang']['noakun']."</th>";
				$tab.= "<th>".$_SESSION['lang']['akun']."</th>";
				$tab.= "<th>BI</th>";
				$tab.= "<th>SD BI</th>";
				$tab.= "<th>Setahun</th>";
				$tab .= "</tr>";
				$tab.="</thead>";
				$tab.="<tbody>";
				
				$wh="";
				if($tt!=''){
					$wh=" and thntnm='".$tt."'";
				}
				
				$n="(";
				for($i=1;$i<=intval($month);$i++){
					$s="rp".addZero($i,2);
					if($i<intval($month)){$n.=$s."+";}else{$n.=$s;}
				}
				$n.=")";

				$p="(rp01+rp02+rp03+rp04+rp05+rp06+rp07+rp08+rp09+rp10+rp11+rp12)";
				$str=" select kodebudget,jumlah,noakun,kodeorg,substr(kodeorg,1,6) as divisi,
				".$n." as sdbi,rp".$month." as bi,rupiah as setahun 
				from ".$dbname.".bgt_budget_detail a left join " . $dbname . ".bgt_blok b 
				on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget  
				where kodeorg like '".$sumber."%' ".$wh." and a.tahunbudget = '".$year."' and noakun like '611%'";

				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					@$data[$bar['noakun']]=$bar['noakun'];
					@$jlhbgtbi[$bar['noakun']]+=$bar['bi'];
					@$jlhbgtsbi[$bar['noakun']]+=$bar['sdbi'];
					@$jlhbgtthn[$bar['noakun']]+=$bar['setahun'];
				}
				
				if(count(@$data)==0){
					exit("Warning : Data Kosong !!!");
				}
				$no=0;
				foreach($data as $akun){
					$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$akun."'");
					$no++;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td align=center>".$akun."</td>";
					$tab.="<td align=left>".$nmakun[$akun]."</td>";
					$tab.="<td align=right>".@number_format($jlhbgtbi[$akun])."</td>";
					$tab.="<td align=right>".@number_format($jlhbgtsbi[$akun])."</td>";
					$tab.="<td align=right>".@number_format($jlhbgtthn[$akun])."</td>";
					
					@$tjlhbgtbi+=$jlhbgtbi[$akun];
					@$tjlhbgtsbi+=$jlhbgtsbi[$akun];
					@$tjlhbgtthn+=$jlhbgtthn[$akun];
				}
				
				$tab.="</tr>";
				$tab.="<tr class=rowcontent style=font-weight:bold>";
				$tab.="<td align=center colspan=3>Total</td>";
				$tab.="<td align=right>".@number_format($tjlhbgtbi)."</td>";
				$tab.="<td align=right>".@number_format($tjlhbgtsbi)."</td>";
				$tab.="<td align=right>".@number_format($tjlhbgtthn)."</td>";
				
					
				$tab.="</fieldset>";
				
				echo $tab;
			break;
		}
	break;
}
?>