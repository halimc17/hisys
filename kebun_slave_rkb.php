<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method', '');
$norkb = checkPostGet('norkb', '');
$unit = checkPostGet('unit', '');
$per = checkPostGet('per', '');

$ttpnn = checkPostGet('ttpnn', '');
$divpnn = checkPostGet('divpnn', '');

@$explper=explode('-',$per);


// function my_operator($a, $b, $char) {
	// switch($char) {
		// case '=': return $a == $b;
		// case '<=': return $a <= $b;
		// case '>=': return $a >= $b;
		// case '<': return $a < $b;
		// case '>': return $a > $b;
	// }
// }


switch ($method) {
	
	
	case'nopnn':
	
	break;
	
	case'detailpnn':
	
		
	
		$str="select * from ".$dbname.".setup_blok where kodeorg like '".$divpnn."%' and tahuntanam='".$ttpnn."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrkdblok[$bar['kodeorg']]=$bar['kodeorg'];
			$tt[$bar['kodeorg']]=$bar['tahuntanam'];
			$luas[$bar['kodeorg']]=$bar['luasareaproduktif'];
		}
		
		
		$str="select * from ".$dbname.".sdm_5tipekaryawan where id in ('2','3','4')  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrtpkar[$bar['id']]=$bar['tipe'];
		}
		
		
		#buat bgt
		$str="select * from ".$dbname.".bgt_produksi_kebun where kodeblok like '".$divpnn."%' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$jjgbgt[$bar['kodeblok']]=$bar['jjg'.$explper[1]];
		}
		
		$str="select * from ".$dbname.".bgt_produksi_kbn_kg_vw where divisi='".$divpnn."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$bjrbgt[$bar['kodeblok']]=$bar['bjr'];
			@$kgbgt[$bar['kodeblok']]=$bar['kg'.$explper[1]];
		}
		
		
		// #cari basis kebun_5basispanen2
		// $str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$_SESSION['empl']['kodeorganisasi']."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// // @$bjrbasis[$bar['bjrrata']]=$bar['bjrrata'];
			// // @$bjrbasis[$bar['bjrrata']]=$bar['bjrdari'];
			// // @$bjrbasis[$bar['bjrrata']]=$bar['bjrsampai'];
			// $arrbasis[$bar['basis']]['awal'] = $bar['bjrdari'];
			// $arrbasis[$bar['basis']]['akhir'] = $bar['bjrsampai'];
		// }
		
		
		
		
		
		
	
		$stream="";
			$stream.="<fieldset><legend><b>Detail Input</b></legend>";
			$stream.="
					<table cellpading=1 cellspacing=1 border=0 class=sortable style=width:70%>
						<thead>
							<tr>
								<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>    
								<td align=center colspan=3>".$_SESSION['lang']['blok']."</td>    
								<td align=center colspan=3>".$_SESSION['lang']['rencana']."</td>    
								<td align=center rowspan=2>".$_SESSION['lang']['basisjjg']."</td>
								<td align=center rowspan=2>".$_SESSION['lang']['jjgoutput']."</td>
								<td align=center colspan=5>".$_SESSION['lang']['jhk']."</td>   
								<td align=center colspan=4>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['karyawan']."</td>   
								<td align=center colspan=4>".$_SESSION['lang']['premi']." ".$_SESSION['lang']['pengawasan']."</td>   
								<td align=center rowspan=2>".$_SESSION['lang']['total']."</td> 
							</tr>
							<tr>
								<td>".$_SESSION['lang']['kodeblok']."</td>
								<td>".$_SESSION['lang']['tahuntanam']."</td>
								<td>".$_SESSION['lang']['luas']."</td>
									
								<td>".$_SESSION['lang']['jjg']."</td>
								<td>".$_SESSION['lang']['bjr']."</td>
								<td>".$_SESSION['lang']['kg']."</td>";
							
								$stream.="<td align=center>PKWT</td>";
								$stream.="<td align=center>PKWTT</td>";
								$stream.="<td align=center>KHL</td>";
								$stream.="<td align=center>BOR</td>";
													
							$stream.="
								<td>".$_SESSION['lang']['total']."</td>
								
								<td>".$_SESSION['lang']['premisiapbasis']."</td>
								<td>".$_SESSION['lang']['premlebihbasis']."</td>
								<td>Premi HM/HB</td>
								<td>".$_SESSION['lang']['total']."</td>
								
								<td>".$_SESSION['lang']['mandor']."</td>
								<td>".$_SESSION['lang']['kerani']."</td>
								<td>".$_SESSION['lang']['mandor']." I</td>
								<td>".$_SESSION['lang']['total']."</td>
							</tr>
						</thead>";
						
			foreach($arrkdblok as $kdblok){
				@$nopnn+=1;
				$stream.="<tr class=rowcontent>";
				$stream.="<td align=center>".$nopnn."</td>";
				$stream.="<td>".$kdblok."</td>";
				$stream.="<td align=right>".$tt[$kdblok]."</td>";
				$stream.="<td align=right>".$luas[$kdblok]."</td>";
				
				$stream.="<td align=left><input type=text value='".@$jjgbgt[$kdblok]."'  onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				$stream.="<td align=left><input type=text value='".@$bjrbgt[$kdblok]."'  onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				$stream.="<td align=left><input type=text value='".@$kgbgt[$kdblok]."' onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				
				if(@$bjrbgt[$kdblok]!=''){
					$str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$_SESSION['empl']['kodeorganisasi']."'
							 and bjrdari <= '".$bjrbgt[$kdblok]."' and bjrsampai >= '".$bjrbgt[$kdblok]."' ";
					$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$basispnn[$kdblok]=$bar['basis'];
						$premibasissetup[$kdblok]=$bar['premibasis'];
						$premilebihbasissetup[$kdblok]=$bar['premilebihbasis'];
				}else{
					$basispnn[$kdblok]=0;
					$premibasissetup[$kdblok]=0;
					$premilebihbasissetup[$kdblok]=0;
				}
				
				$stream.="<td align=right>".$basispnn[$kdblok]."</td>";
				
				
				$jjgpnn[$kdblok]=number_format($basispnn[$kdblok]*115/100);
				$stream.="<td align=left><input type=text value='".$jjgpnn[$kdblok]."' onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				
				
				$defaultkarpnn[$kdblok]=number_format($jjgbgt[$kdblok]/$jjgpnn[$kdblok]);
				
				
				
				$stream.="<td align=left><input type=text value=0 id=pkwtpnn".$nopnn." onkeyup=totkarpnn('".$nopnn."')  onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				$stream.="<td align=left><input type=text value='".$defaultkarpnn[$kdblok]."' id=pkwttpnn".$nopnn." onkeyup=totkarpnn('".$nopnn."')  onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				$stream.="<td align=left><input type=text value=0 id=khlpnn".$nopnn." onkeyup=totkarpnn('".$nopnn."')  onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				$stream.="<td align=left><input type=text value=0 id=borpnn".$nopnn." onkeyup=totkarpnn('".$nopnn."')  onkeypress='return angka_doang(event)' class=myinputtextnumber style=\"width:55px;\"></td>";
				$stream.="<td align=right id=tkarpnn".$nopnn.">".$defaultkarpnn[$kdblok]."</td>";
				
				$premibasis[$kdblok]=$premibasissetup[$kdblok]*$defaultkarpnn[$kdblok];
				
				$pemilebihbasis[$kdblok]=($jjgbgt[$kdblok]-($defaultkarpnn[$kdblok]*$basispnn[$kdblok]))*$premilebihbasissetup[$kdblok];
				
				
				$stream.="<td align=right>".$premibasis[$kdblok]."</td>";
				$stream.="<td align=right>".$pemilebihbasis[$kdblok]."</td>";
				$stream.="<td align=right></td>";
				
				$tpremi[$kdblok]=$premibasis[$kdblok]+$pemilebihbasis[$kdblok];
				
				$stream.="<td align=right>".$tpremi[$kdblok]."</td>";
				
				
				
		
				$premimandor[$kdblok]=$tpremi[$kdblok]/25*1.5;
				$premikerani[$kdblok]=$tpremi[$kdblok]/25*1.25;
				$premimandorsatu[$kdblok]=($premimandor[$kdblok]+$premikerani[$kdblok])*1.5;
				$tpremiawas[$kdblok]=$premimandor[$kdblok]+$premikerani[$kdblok]+$premimandorsatu[$kdblok];
				$gtpremi[$kdblok]=$tpremiawas[$kdblok]+$tpremi[$kdblok];
				
				$stream.="<td align=right>".$premimandor[$kdblok]."</td>";
				$stream.="<td align=right>".$premikerani[$kdblok]."</td>";
				$stream.="<td align=right>".$premimandorsatu[$kdblok]."</td>";
				$stream.="<td align=right>".$tpremiawas[$kdblok]."</td>";
				$stream.="<td align=right>".$gtpremi[$kdblok]."</td>";
				
				
				$stream.="</tr>";

			}			
						
						
			echo $stream;			
	
	
	break;
	
	
	
	
	
	
	
	
	
    case'loaddata':

        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
		
		
		
		$where="";
        if($thnsch!='') {
			$where.=" and periode like '".$thnsch."%' ";
        }

        $str="select count(*) as jmlhrow from ".$dbname.".keu_pdoht where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ".$where." group by nopdo  ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	


        $no=0;
		$no=$maxdisplay;
        $str="SELECT * from ".$dbname.".keu_pdoht where kodeorg like '".$_SESSION['empl']['lokasitugas']."%' ".$where." group by nopdo   limit ".$offset.",".$limit."";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['kodeorg']."</td>";
			$tab.="<td align=center>".$bar['periode']."</td>";
			$tab.="<td align=left>".$bar['nopdo']."</td>";
            $tab.="
            <td align=center>";
			if($bar['posting']==1){
				$tab.="
					<img src=images/skyblue/posted.png class=zImgOffBtn title='Posted');\">  
					<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detail('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','html','event');\">         					
				";
			}
			else{
				$tab.="
				<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
                     onclick=\"edit('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."');\">
                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
                     onclick=\"deletehead('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."');\">
                <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
                     onclick=\"detail('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."','html','event');\">         
                <img src=images/skyblue/posting.png class=zImgBtn title='Posting' 
                     onclick=\"posting('".$bar['nopdo']."','".$bar['kodeorg']."','".$bar['periode']."');\">           
				";
			}
            $tab.="</td>";
            $tab.="</tr>";
        }
        $totrows=ceil($jlhbrs/$limit);
        if($totrows==0){
                $totrows=1;
        }
        $isiRow='';
        for($er=1;$er<=$totrows;$er++){
                $sel = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd="
            <tr><td colspan=40 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
    
    
    default;
	
}
?>