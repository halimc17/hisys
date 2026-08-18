<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zJournal.php');

$method=checkPostGet('method','');
$pt=checkPostGet('pt','');
$npwp=checkPostGet('npwp','');
$thn=checkPostGet('thn','');
$tipe=checkPostGet('tipe','');
$kodelaporan='TAX PAYMENT';
$param=$_POST;

$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');

$stream="";

switch($method){


	case'getnpwp':
		$optnpwp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";	
		$str="SELECT distinct(npwp) as npwp FROM ".$dbname.".keu_tagihanht where kodeorg='".$pt."' and (npwp!='' and npwp!='false') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optnpwp.="<option value='".$bar['npwp']."'>".$bar['npwp']."</option>";
		}
		echo $optnpwp;
	break;
	
	case'preview':
	
		$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrno[$bar['nourut']]=$bar['nourut'];
			$keydata[$bar['nourut']]=$bar['keydata'];
			$nmurutin[$bar['nourut']]=$bar['keterangandisplay'];
			$nmuruten[$bar['nourut']]=$bar['keterangandisplay1'];
		}
		#= ambil daftar noakun
		
		$daftarakun=array();
		$nouruttemp='';
		
		$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' group by nourut";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$jumlahdaftar[$bar['nourut']]=$bar['jumlah'];
		}
		
		$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$daftarakun[$bar['nourut']]=$bar['noakun'];
			$nouruttemp=$bar['nourut'];
		}
			// echo"<pre>";
			// print_r($daftarakun);
			// echo"</pre>";
			
		
			#= sumber tagihan
			$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='TX' and	kodeparameter='LAPTXKEUTG'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();	
				$arrbpjs=explode(',',$bar['nilai']);
				foreach($arrbpjs as $key){
					$arrtg[]=$key;
				}
				
			
		
			
			
			#= 0 = default yg bersumber dari noakun detail
			#= 1 = vateinout
			#= 2 = tagihan
			 
			foreach($arrno as $nourut){
				$case=0;
				if(in_array($nourut,$arrtg)){
					$case=1;
				}
				switch($case){
					case'1';
						$str="select a.tanggal,substr(a.tanggal,6,2) as bulan,a.jumlah,tanggalpengajuan,a.noakun as noakun 
								from ".$dbname.".keu_tagihanht b 
								left join ".$dbname.".keu_kasbankdtht_vw a on b.noinvoice=a.keterangan1 
								where tipeinvoice ='".$keydata[$nourut]."' and b.kodeorg='".$pt."' 
								and b.npwp=npwp and b.npwp='".$npwp."' and b.tanggal like '".$thn."%' and  a.noakun is not null";
								//echo $str;
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						while($bar=$res->fetch()){
							$amount[$nourut][intval($bar['bulan'])]+=$bar['jumlah'];
							$pdate[$nourut][intval($bar['bulan'])]=$bar['tanggal'];
							$sdate[$nourut][intval($bar['bulan'])]=$bar['tanggalpengajuan'];
							$daftarakun[$nourut]=$bar['noakun'];
						}

					break;
					
					case'0';
						$str="select tanggal,jumlah,noakun,substr(tanggal,6,2) as bulan,tanggalpengajuan 
								from ".$dbname.".keu_kasbankdtht_vw where tanggal like '".$thn."%' 
								and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') 
								and noakun in (".$daftarakun[$nourut].") and jumlah>0 and tipetransaksi='K'";
								// echo $str;
								// exit('warning');
						$res=fetchData($str);
						if(count($res)!=0){
							foreach ($res as $key => $bar) {
								$amount[$nourut][intval($bar['bulan'])]+=$bar['jumlah'];
								$pdate[$nourut][intval($bar['bulan'])]=$bar['tanggal'];
								$sdate[$nourut][intval($bar['bulan'])]=$bar['tanggalpengajuan'];
							}
						}
					break;
					
				}
			}
			
			#============ tampil data
			
			if($tipe=='excel'){
				$border='border=1';
			} else {
				$border='';
			}
			
			$stream.= "<table class=sortable ".$border." cellspacing=1>";// style=width:100%
			$stream.="<thead>";	
			$stream.="<tr class=rowheader>";	 
				$stream.="<td align=center colspan=2 rowspan=2>".$_SESSION['lang']['desc']."</td>";
				$stream.="<td align=center colspan=12>".$_SESSION['lang']['bulan']."</td>";
				$stream.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
			$stream.="</tr>";  
			$stream.="<tr class=rowheader>";
				for($i=1;$i<=12;$i++){
					$stream.="<td align=center>".numToMonth($i,$lang='E',$format='long')."</td>";
				}
			$stream.="</tr>";  
			$stream.="</thead>";
		
		
			
			foreach($arrno as $nourut){
				$stream.="<tr class=rowcontent>";	
					$stream.="<td rowspan=3>".$nmurutin[$nourut]."<br>".$nmuruten[$nourut]."</td>";  	
					$stream.="<td>".$_SESSION['lang']['amount']."</td>";  	
					for($i=1;$i<=12;$i++){
						$stream.="<td align=right>".number_format($amount[$nourut][$i])."</td>";  	
						$tamount[$nourut]+=$amount[$nourut][$i];
					}
					$stream.="<td align=right>".number_format($tamount[$nourut])."</td>";  	
					 	
				$stream.="</tr>";	
				$stream.="<tr class=rowcontent>";	
					$stream.="<td>Paid Date</td>";
					for($i=1;$i<=12;$i++){
						$isiTgl="";
						if($pdate[$nourut][$i]!=''){
							$isiTgl=tanggalnormal($pdate[$nourut][$i]);
						}
						$stream.="<td align=right  id='tgl_".$nourut."_".$i."'>".$isiTgl."</td>";
					} 	
					$stream.="<td rowspan=2></td>"; 
				$stream.="</tr>";	
				$stream.="<tr class=rowcontent>";	
					$stream.="<td>Submit Date</td>";
					for($i=1;$i<=12;$i++){
						if($i<10){
							$prddt="0".$i;
						}else{
							$prddt=$i;
						}
						$tglisinya2="";
						$addisiTgl="";
						$scek="select * from ".$dbname.".tax_submitdate where periode='".$thn."-".$prddt."' and kodept='".$pt."' and noakun='".$nourut."'";
						$rcek=fetchData($scek);
						if(count($rcek)!=0){
							$tglisinya=tanggalnormal($rcek[0]['tanggal']);
							$addisiTgl=" value='".$tglisinya."'";
							$tglisinya2=$rcek[0]['tanggal'];
						}
						if($tipe!='excel'){
							$stream.="<td align=right><input type=text class=myinputtext id=".$nourut."_".$i." onmousemove=setCalendar(this.id) onkeypress=return false; onchange=addTgl('".$nourut."','".$thn."-".$prddt."','".$pt."') ".$addisiTgl." style=width:60px; maxlength=10 /></td>";  	// 
						}else{
							$stream.="<td align=right>".$tglisinya2."</td>";  	// 
						}
					}
				$stream.="</tr>";	
			}
			
		
		$stream.="</table>";  
		
		
		if($tipe=='excel'){
			$tglSkrg=date("YmdHis");
			$nop_="summary_".$pt."_".$tglSkrg;
			if(strlen($stream)>0){
                if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
								@unlink('tempExcel/'.$file);
						}
					}	
					closedir($handle);
                }
                $handle=fopen("tempExcel/".$nop_.".xls",'w');
                if(!fwrite($handle,$stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
                } else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
                }
                fclose($handle);
			}     
		} else {
			echo $stream;
		}
	
	break;
	case'addTgl':
		$tglisinya="00-00-0000";
		$whr="periode='".$param['periode']."' and kodept='".$param['pt']."' and noakun='".$param['noakun']."'";
		$scek="select * from ".$dbname.".tax_submitdate where ".$whr;
		$rcek=fetchData($scek);
		if(count($rcek)!=0){
			$supdate="update ".$dbname.".tax_submitdate set tanggal='".tanggalsystemn($param['tanggal'])."' where ".$whr;
		}else{
			$supdate="insert into ".$dbname.".tax_submitdate values('".$param['pt']."','".$param['periode']."','".$param['noakun']."','".tanggalsystemn($param['tanggal'])."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
		}
		try{$owlPDO->exec($supdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n".$supdate; die(); }
	break;
}



?>