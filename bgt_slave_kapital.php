<?php 
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;
$id   = checkPostGet('id','');

switch($_POST['proses']){
	case'fillfield':
		$str = "select * from ".$dbname.".bgt_kapital where kunci='".$id."'"; #exit("error".$str);
		$res=fetchdata($str);
		foreach($res as $bar){
			$optnmarus=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar['aruskas']."'");
			
			echo $bar['tahunbudget']."##".$bar['kodeunit']."##".$bar['jeniskapital']."##".$bar['aruskas']."##".$optnmarus[$bar['aruskas']]."##".$bar['jumlah']."##".$bar['hargasatuan']."##".$bar['hargatotal']."##".$bar['lokasi']."##".$bar['keterangan'];			
		}
	break;
	
	case'getlokasi':
		$optlokasi="<option value=''></option>";
		$str="select namaorganisasi,kodeorganisasi,tipe from ".$dbname.".organisasi where 1=1 and kodeorganisasi like '".$param['kodeorg']."%' order by length(kodeorganisasi) asc, tipe";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			$d=$bar->tipe;
			if($d!=$n){			
				$optlokasi.="<optgroup label='".$d." - ".$d."'>";
			}
			
			$optlokasi.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
			
			$n=$d;
			if($d!=$n){			
				$optlokasi.="</optgroup>";
			}
		}
		echo $optlokasi;
	break;
	case'getaruskas':
		$optakun=makeOption($dbname,'sdm_5tipeasset','kodetipe,akunak');
		$str = "select distinct a.noaruskas, a.nama_aruskas from ".$dbname.".keu_5aruskas a left join ".$dbname.".keu_5aruskas_detail b on a.noaruskas=b.noaruskas where 1=1 and a.tipetransaksi='K' and a.level='3' and a.status='1' and b.noakun = '".$optakun[$param['kodebgt']]."' order by a.noaruskas asc"; #exit("error".$str);
		$res=fetchdata($str);
		if(count($res)=='0'){
			exit("Warning : Nomor aruskas untuk akun ".$optakun[$param['kodebgt']]." belum ada.");
		}
		
		$optaruskas="<option value=''></option>";
		foreach($res as $bar){
			$optaruskas.="<option value=".$bar['noaruskas'].">".$bar['noaruskas']." - ".$bar['nama_aruskas']."</option>";
		}
		echo $optaruskas;
		
	break;
	case 'simpanHeader':
	if($_POST['aruskas']==''){
		exit("warning : Arus kas tidak boleh kosong.");
	}
	
	$str="insert into ".$dbname.".bgt_kapital (tahunbudget, kodeunit, jeniskapital, keterangan, jumlah, hargasatuan, hargatotal, tutup,updateby,lokasi,aruskas)
	 values(".$_POST['tahunbudget'].",'".$_POST['kodeorg']."','".$_POST['jeniskapital']."','".$_POST['keterangan']."',
	 ".$_POST['jumlah'].",".$_POST['harga'].",".$_POST['total'].",0,".$_SESSION['standard']['userid'].",'".$_POST['lokasi']."','".$_POST['aruskas']."');";
	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

	break;
	case 'edit':
		if($_POST['aruskas']==''){
			exit("warning : Arus kas tidak boleh kosong.");
		}
		$data = array();
		$data = array(
			'tahunbudget' =>$_POST['tahunbudget'],
			'kodeunit'    =>$_POST['kodeorg'],
			'jeniskapital'=>$_POST['jeniskapital'],
			'keterangan'  =>$_POST['keterangan'],
			'jumlah'      =>$_POST['jumlah'],
			'hargasatuan' =>$_POST['harga'],
			'hargatotal'  =>$_POST['total'],
			'tutup'       =>'0',
			'updateby'    =>$_SESSION['standard']['userid'],
			'lokasi'      =>$_POST['lokasi'],
			'aruskas'     =>$_POST['aruskas']
		);
		
		$where = "kunci='".$id."'";
		$str = updateQuery($dbname,'bgt_kapital',$data,$where);
		try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		
		$str="select a.*, hargatotal, sum(a.k01+a.k02+a.k03+a.k04+a.k05+a.k06+a.k07+a.k08+a.k09+a.k10+a.k11+a.k12) as sebaran from ".$dbname.".bgt_kapital a where kunci='".$id."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$str="update ".$dbname.".bgt_kapital set
				 k01=".$bar['k01']/$bar['sebaran']*$_POST['total'].",
				 k02=".$bar['k02']/$bar['sebaran']*$_POST['total'].",
				 k03=".$bar['k03']/$bar['sebaran']*$_POST['total'].",
				 k04=".$bar['k04']/$bar['sebaran']*$_POST['total'].",
				 k05=".$bar['k05']/$bar['sebaran']*$_POST['total'].",
				 k06=".$bar['k06']/$bar['sebaran']*$_POST['total'].",
				 k07=".$bar['k07']/$bar['sebaran']*$_POST['total'].",
				 k08=".$bar['k08']/$bar['sebaran']*$_POST['total'].",
				 k09=".$bar['k09']/$bar['sebaran']*$_POST['total'].",
				 k10=".$bar['k10']/$bar['sebaran']*$_POST['total'].",
				 k11=".$bar['k11']/$bar['sebaran']*$_POST['total'].",
				 k12=".$bar['k12']/$bar['sebaran']*$_POST['total'].",             
				 updateby=".$_SESSION['standard']['userid']." 
			where kunci=".$id;
            try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
		
		$str="select a.*, hargatotal, sum(a.k01+a.k02+a.k03+a.k04+a.k05+a.k06+a.k07+a.k08+a.k09+a.k10+a.k11+a.k12) as sebaran from ".$dbname.".bgt_kapital a where kunci='".$id."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$hargatotal=$bar['hargatotal'];
			$sebaran=$bar['sebaran'];
		}	
		if(round($hargatotal)!=round($sebaran)){
			echo ($hargatotal-$sebaran); 
		}
		#exit("error");
	break;
	
	case 'delete':
      $str="delete from ".$dbname.".bgt_kapital where kunci=".$_POST['kunci'];
      try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
      
	break;
	case 'sebaran':
        $str="select * from ".$dbname.".bgt_kapital where kunci=".$_POST['kunci'];
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
           $kunci=$bar->kunci;
           $total=$bar->hargatotal;
           $k01=$bar->k01;
           $k02=$bar->k02;
           $k03=$bar->k03;
           $k04=$bar->k04;
           $k05=$bar->k05;
           $k06=$bar->k06;
           $k07=$bar->k07;
           $k08=$bar->k08;
           $k09=$bar->k09;
           $k10=$bar->k10;
           $k11=$bar->k11;
           $k12=$bar->k12;
           $krata=$total/12;
       }

           echo"<table class=sortable cellspacing=1 border=0>
                <thead>
                <thead>
                   <tr class=rowheader><td>".$_SESSION['lang']['bulan']."</td><td>%</td><td>".number_format($total,2)."</td></tr>
                </thead>
                </thead>
                <tbody>
                <tr class=rowcontent>";
           if(($k01+$k02+$k03+$k04+$k05+$k06+$k07+$k08+$k09+$k10+$k11+$k12)<1)
           {
               for($x=1;$x<13;$x++){
                   $z=str_pad($x, 2, "0", STR_PAD_LEFT);
                    echo"<tr class=rowcontent><td>".$z."</td>
                          <td><input type=text class=myinputtextnumber onkeypress=\"return angka_doang(event);\" id=persen".$x." size=3 onblur=ubahNilai(".$total.") value=".number_format(($krata/$total*100),2,'.','')."></td>
                          <td><input id=k".$x." type=text class=myinputtextnumber onkeypress=\"return angka_doang(event)\" value='".$krata."' size=15></td></tr>";
               }
                 
            }
            else
            {
               for($x=1;$x<13;$x++){
                   $z=str_pad($x, 2, "0", STR_PAD_LEFT);
                    echo"<tr class=rowcontent><td>".$z."</td>
                        <td><input type=text class=myinputtextnumber onkeypress=\"return angka_doang(event);\" id=persen".$x." size=3 onblur=ubahNilai(".$total.") value=".number_format((${"k".$z}/$total*100),2,'.','')."></td>
                        <td><input id=k".$x." type=text class=myinputtextnumber onkeypress=\"return angka_doang(event)\" value='".${"k".$z}."' size=15></td></tr>";
               }  
            }   
            echo "<tr class=rowcontent><td colspan=3 align=center>
                        <img id='detail_add' title='Simpan' class=zImgBtn onclick=simpanSebaran('".$total."','".$kunci."') src='images/save.png'/ style='cursor:pointer;'>&nbsp;&nbsp;<img id='detail_add' title='Clear Form' class=zImgBtn  width='16' height='16'  onclick=\"clearForm()\" src='images/clear.png'/ style='cursor:pointer;'>";

           echo"</tr>
                </tbody>
                <tfoot>
                </tfoot>
               </table>";
      break; 
   case 'updatesebaran':
       $zz=$_POST['k01']+$_POST['k02']+$_POST['k03']+$_POST['k04']+$_POST['k05']+$_POST['k06']+$_POST['k07']+$_POST['k08']+$_POST['k09']+$_POST['k10']+$_POST['k11']+$_POST['k12'];
       if(floor($zz)>$_POST['total'])
         exit("Error: Sebaran lebih besar dari total (".$_POST['total']."<".$zz.")");
       else
       {   
           $str="update ".$dbname.".bgt_kapital set
             k01=".$_POST['k01'].",
             k02=".$_POST['k02'].",
             k03=".$_POST['k03'].",
             k04=".$_POST['k04'].",
             k05=".$_POST['k05'].",
             k06=".$_POST['k06'].",
             k07=".$_POST['k07'].",
             k08=".$_POST['k08'].",
             k09=".$_POST['k09'].",
             k10=".$_POST['k10'].",
             k11=".$_POST['k11'].",
             k12=".$_POST['k12'].",
             updateby=".$_SESSION['standard']['userid']."    
             where kunci=".$_POST['kunci'];
			 
             try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
       }
      break; 
  case'tutup':
      $str="update ".$dbname.".bgt_kapital set tutup=1,updateby=".$_SESSION['standard']['userid']." 
          where kodeunit='".$_SESSION['empl']['lokasitugas']."' and tahunbudget='".$_POST['tahun']."'";
      try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
             
      break;

    case'loadData':
	if($_SESSION['language']=='EN'){
			$dd='namatipe1 as namatipe';
	}else{
			$dd='namatipe as namatipe';
	}
	echo"<div id=container>
					<table cellpadding=5 cellspacing=1 border=0 class=sortable>
					<thead>
					<tr class=rowheader>
							<td align=center>".substr($_SESSION['lang']['nomor'],0,2)."</td>
							<td align=center width=50px>".$_SESSION['lang']['budgetyear']."</td>
							<td align=center>".$_SESSION['lang']['unit']."</td>
							<td align=center>".$_SESSION['lang']['lokasi']."</td>                 
							<td align=center>".$_SESSION['lang']['jnsKapital']."</td>
							<td align=center>".$_SESSION['lang']['keterangan']."</td>   
							<td align=center>".$_SESSION['lang']['aruskas']."</td>   
							<td align=center>".$_SESSION['lang']['jumlah']."</td>
							<td align=center>".$_SESSION['lang']['hargasatuan']."</td>
							<td align=center>".$_SESSION['lang']['total']."</td>
							<td align=center>".$_SESSION['lang']['sebaran']."</td>    
							<td align=center colspan=3>".$_SESSION['lang']['action']."</td>
					</tr>
					</thead><tbody id=container1>";

	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$where = "";
	} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
		$where = "";
	} else {
		$where = " and kodeunit = '".$_SESSION['empl']['lokasitugas']."'";
	}
	
	$sCount="select * from ".$dbname.".bgt_kapital a left join
			   ".$dbname.".sdm_5tipeasset b on a.jeniskapital=b.kodetipe
			   where 1=1 ".$where."";
	$resx=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($resx);
	$vCount = $numrows;

	$limit=15;
	$page=0;
	if(isset($_POST['page'])){
		$page=$_POST['page'];
		if($page<0){
				$page=0;
		}
	}

	$offset=$page*$limit;
	$maxdisplay=($page*$limit);

	$str="select a.*,b.".$dd.",
			(a.k01+a.k02+a.k03+a.k04+a.k05+a.k06+a.k07+a.k08+a.k09+a.k10+a.k11+a.k12) as sebaran
			from ".$dbname.".bgt_kapital a left join
			".$dbname.".sdm_5tipeasset b on a.jeniskapital=b.kodetipe
			where 1=1 ".$where."
			order by tahunbudget desc limit ".$offset.",".$limit."";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$no=0;

	while($bar=$res->fetch()){
			$bar->tutup==0?$rtp=" title=\"Sebaran\" onclick=\"sebaran(".$bar->kunci.",event)\" style='cursor:pointer;'":$rtp='';
			$no+=1;
			$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas',"noaruskas='".$bar->aruskas."'");
			echo"<tr class=rowcontent>
				<td align=center ".$rtp.">".$no."</td>
				<td align=center ".$rtp.">".$bar->tahunbudget."</td>
				<td ".$rtp.">".$bar->kodeunit."</td>
				<td ".$rtp.">".$bar->lokasi."</td>    
				<td ".$rtp.">".$bar->namatipe."</td>
				<td ".$rtp.">".$bar->keterangan."</td>
				<td ".$rtp.">".$nmaruskas[$bar->aruskas]."</td>
				<td align=right ".$rtp.">".number_format($bar->jumlah,0)."</td>
				<td align=right ".$rtp.">".number_format($bar->hargasatuan,0)."</td>
				<td align=right  ".$rtp.">".number_format($bar->hargatotal,0)."</td>";

			if(round($bar->sebaran) < round($bar->hargatotal)){
				echo"<td align=center><img title='Belum ada kalenderisasi' onclick=\"sebaran(".$bar->kunci.",event)\" class=zImgBtn src='images/stop1.png'/></td>";
			}else{
				echo"<td align=center><img title='Sudah ada kalenderisasi' class=zImgBtn src='images/tick_64.png'/></td>";
			}

			if($bar->tutup==1){
					echo"<td></td>";
					echo"<td></td>";
					echo"<td></td>";
			}else{
					echo"<td align=center width=20px><img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$bar->kunci."');\"></td>";
					echo"<td align=center width=20px style='cursor:pointer;'>
						<img id='detail_add' title='delete data' class=zImgBtn onclick=\"deleteData('".$bar->kunci."')\" src='images/application/application_delete.png'/></td>
						<td align=center width=20px><img id=\"search\" src='images/skyblue/zoom.png' class='zImgBtn' title=\"Sebaran\" onclick=\"sebaran(".$bar->kunci.",event)\" type=\"image\">
						</td>";
			}

			echo"</tr> ";
	}


	echo"</tbody>
			<tr>
					<td colspan=14 style='text-align:center;'>
							".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $vCount."<br />
							<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
							<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					</td>
			</tr></table></div>";
    break;
	case'rekap':
		$tab="";
		
		$str="select namatipe, sum(hargatotal) as hargatotal, jeniskapital, kodeunit, tahunbudget from ".$dbname.".bgt_kapital a left join ".$dbname.".sdm_5tipeasset b on a.jeniskapital=b.kodetipe where kodeunit='".$_SESSION['empl']['lokasitugas']."' and tahunbudget='".$_POST['tahun']."' group by namatipe order by tahunbudget desc";
		
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $no = 0;
        while ($bar = $res->fetch()) {
            $no+=1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=center>".$bar['tahunbudget']."</td>";
            $tab.="<td>" . $bar['kodeunit'] . "</td>";            
            $tab.="<td>" . $bar['namatipe'] . "</td>";            
            $tab.="<td align=right>".@number_format($bar['hargatotal'])."</td>";
			
			@$ttl+=$bar['hargatotal'];
			
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=4><b>TOTAL</b></td>";
		$tab.="<td align=right><b>".@number_format($ttl)."</b></td>";
		echo $tab;
			
	break;
  }
?>