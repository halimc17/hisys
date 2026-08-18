<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$id_parent= checkPostGet('id_parent', '');
$id       = checkPostGet('id','');
$kodeorg  = checkPostGet('kodeorg', '');
$kodeorg1 = checkPostGet('kodeorg1', '');
$kodeorght= checkPostGet('kodeorght', '');
$tanggal  = tanggalsystemn(checkPostGet('tanggal',''));
$tanggal1 = tanggalsystemn(checkPostGet('tanggal1',''));
$tanggal2 = tanggalsystemn(checkPostGet('tanggal2',''));
$tanggalht= tanggalsystemn(checkPostGet('tanggalht',''));
$jenis    = checkPostGet('jenis', '');
$jenis1   = checkPostGet('jenis1', '');
$jam      = checkPostGet('jam', '');
$menit    = checkPostGet('menit', '');
$tekanan  = checkPostGet('tekanan', '');
$method   = checkPostGet('method', '');
$toexcel  = checkPostGet('toexcel', '');


function findDataByDate($date1,$date2,$jenis,$datalist,$tanggallview){
    	$result = array();
    	for($i=0; $i<count($datalist); $i++){
    		if($tanggallview == $datalist[$i]['tanggal']){
	    		$tanggal =$datalist[$i]['jam'];
	    		if($tanggal >= $date1 and $tanggal < $date2 and $jenis==$datalist[$i]['jenis']){
	    			$result = $datalist[$i]['datalist'];
	    			break;
	    		}
	    	}
    	}
    	return $result;
    }
    
//exit('error'.$str);

switch ($method) {
    case 'insert':
	    	$str1 = "select id from " . $dbname . ".pabrik_tekananbolerht 
	    	where jenis = '".$jenis."'
	    	and kodeorg = '".$kodeorght."'
	    	and tanggal = '".$tanggalht."'";
	    	//exit('error'.$str1);
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			$jml = $res1->rowCount();
			$getId = 0;
			if($jml ==0){

		        $str = "insert into " . $dbname . ".pabrik_tekananbolerht (kodeorg,tanggal,jenis,updateby,createdby,createdtime)
			      values('" . $kodeorght . "','" . $tanggalht . "','" . $jenis . "','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
		        
		        try{
					$owlPDO->exec($str);
					$getId = $owlPDO->lastInsertId();  
				}
				catch (PDOException $e){
					echo " Gagal," . addslashes($e->getMessage());
				}
			}else{

				$bar1 = $res1->fetch();
				$getId = $bar1->id;  
			}
			echo $getId;
        break;

    case 'preview':
    	if(checkPostGet('tanggal1','') == ""){
    		exit("error: Tanggal is Null");
    	}
    	$idht = "";
    	$where = "";
    	$ht    = array();
    	$dataht = array();
    	if($tanggal2 == "" || $tanggal2 == "--"){
    		$where = " and tanggal = '".$tanggal1."'";
    		$tanggal2 = $tanggal1;
    	}else{
    		$where = " and tanggal between '".$tanggal1."' and '".$tanggal2."'";
    	}
    	$str = "select id,kodeorg,tanggal,jenis from " . $dbname . ".pabrik_tekananbolerht
		where kodeorg = '".$kodeorg."' ".$where." order by tanggal desc";
		//exit("ERROR:".$str);
		$res1 = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res1->fetch()){	
			$ht[] 		= "'".$bar['id']."'";
			$t['id'] = $bar['id'];
			$t['kodeorg'] = $bar['kodeorg'];
			$t['jenis'] = $bar['jenis'];
			$t['tanggal'] = $bar['tanggal'];
			$dataht[] = $t;
		}
		$idht = implode(",", $ht); // idht in ('1','2','3')
		$rangeTgl=range(strtotime($tanggal1),strtotime($tanggal2),24*60*60);

if ($toexcel == 'yes')
{
    $border=1;
}
else 
{
    $border=0;
}


    $tab="<table class=sortable cellpading=1 cellspacing=1 border=".$border.">
            <thead>
                <tr class=rowheader>
                	
					<td align=center rowspan=2>" . $_SESSION['lang']['jam'] . "</td>";
					
		//loop date
	for($i=0;$i<count($rangeTgl);$i++){
		$tab.="	<td align=center colspan=2>" . date('Y-m-d',$rangeTgl[$i]) . "</td>";
	}
	$tab.=" </tr>
			<tr>";
			//looping by date range
	for($i=0;$i<count($rangeTgl);$i++){		
       		$tab.="		<td align=center>BOILER" . $_SESSION['lang']['Boiler'] . "</td>
					<td align=center>BPV" . $_SESSION['lang']['BPV'] . "</td>";
	}
  	$tab.=" </tr>
            
            </thead>
             <tbody>";                
                     
    	$data = array();
		$strd = "select * from " . $dbname . ".pabrik_tekananbolerdt 
		where idht in (".$idht.")";
		//exit('eror'.$strd);
		$res2 = $owlPDO->query($strd) or die(print " Gagal: " . PDOException::getMessage());
		$res2->setFetchMode(PDO::FETCH_ASSOC);
		while($detail=$res2->fetch()){
			$getdataht['tanggal'] = "";
			$getdataht['jenis'] = "";
			for($i=0; $i<count($dataht); $i++){
				if($detail['idht'] == $dataht[$i]['id']){
					$getdataht = $dataht[$i];
					break;
				}
			}
			$d['tanggal'] = $getdataht['tanggal'];
			$d['jam'] = $detail['jam'];
			$d['jenis'] = $getdataht['jenis']; 
			$d['datalist'] = $detail; 
			$data[] = $d;
		}
		/*echo '<pre>';
		print_r($rangeTgl[$i]);
		echo '</pre>';*/
		

			$esok = date("Y-m-d",strtotime("+1 day",strtotime($tanggal1)));
			$range=range(strtotime($tanggal1." 07:00"),strtotime($esok." 06:00"),60*60);

					
				for($x=0;$x<count($range);$x++)
				{
					
				$jam1= date("H:i",$range[$x]);
				$jam2=date("H:i",strtotime("+1 hours",$range[$x]));
				$rangedate =$jam1."-".$jam2;
				
			
				$tab.="<tr class='rowcontent'>
						
						
						
						<td>".$rangedate."</td>
						
						";
						
						
					for($i=0;$i<count($rangeTgl);$i++){
					$tab.="<td>".findDataByDate($jam1.":00",$jam2.":00",'BOILER',$data,date('Y-m-d',$rangeTgl[$i]))['nilaitekanan']."</td>
						  <td>".findDataByDate($jam1.":00",$jam2.":00",'BPV',$data,date('Y-m-d',$rangeTgl[$i]))['nilaitekanan']."</td>";
					}
					
				$tab.="</tr>";
			}
	
			echo"</tbody>
            <tfoot id=footData>
             </tfoot>
             </table>";
		$cek = "select * from " . $dbname . ".pabrik_tekananbolerdt 
		where idht in (".$idht.")";
		//exit('eror'.$cek);
		$res2 = $owlPDO->query($strd) or die(print " Gagal: " . PDOException::getMessage());
		$res2->setFetchMode(PDO::FETCH_ASSOC);
		  while ($bar=$res2->fetch())
			{
				$jam[$bar['jam']];
			}
			print_r($jam);

            break;

   case 'preview1':
		$mytgl = checkPostGet('tanggal','');
		
		if($mytgl != ""){
    		$tanggalku = " and tanggal = '".tanggalsystem($mytgl)."'";
    	}
		$limit=15;
		$page=0;
		if(isset($_POST['page'])){
			$page=$_POST['page'];
			if($page<0)
			$page=0;
		}
		$offset=floatval($page)*$limit;
		$maxdisplay=(floatval($page)*$limit);
		$no = $maxdisplay;
		
		echo"<table class=sortable cellspacing=1 cellpadding=5 border=0>
            <thead>
             
			<tr>
       				<th align=center>No</th>
       				<th align=center>" . $_SESSION['lang']['kodeorg'] . "</th>
       				<th align=center>" . $_SESSION['lang']['namaorganisasi'] . "</th>
					<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>
					<th align=center>" . $_SESSION['lang']['jenis'] . "</th>
					<th align=center colspan=2 style='width:50px;'>".$_SESSION['lang']['action']."</th>
  			</tr>
            
            </thead>";                
                     
    	
        $str = "select id,kodeorg,tanggal,jenis from " . $dbname . ".pabrik_tekananbolerht
		where kodeorg = '".$kodeorg1."' ".$tanggalku." order by tanggal desc limit ".$offset.",".$limit."";
		// print_r($str);
		$res1 = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res1->fetch()){		
			$no++;
			echo "<tr class='rowcontent'>
			<td align=center>".$no."</td>
			<td>".$kodeorg1."</td>
			<td>".getNamaOrg($kodeorg1)."</td>
			<td>".tanggalnormal($bar['tanggal'])."</td>
			<td>". $bar['jenis'] ."</td>
			<td align=center width=25px>
				<img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"edit('" . $bar['id'] . "','" . $kodeorg1 . "','" .tanggalnormal($bar['tanggal']). "','" . $bar['jenis']. "');\">
			</td>
			<td align=center width=25px>
				<img src=images/application/application_delete.png class=resicon caption='Edit' onclick=\"deletedata('".$bar['id']."');\">
			</td>
			
		  </tr>";
		}
		
		echo"
            <tr class=rowheader><td colspan=23 align=center>
            ".((floatval($page)*$limit)+1)." to ".((floatval($page)+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=priview1(".(floatval($page)-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=priview1(".(floatval($page)+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
			
			echo"</tbody>
            <tfoot id=footData>
             </tfoot>
             </table>";
    break;


    case 'loaddetail':
   	 	echo"
   	 	<input  type=hidden id='parentid' value='".$id_parent."'>
   	 	<fieldset style='width:320px;' id=listDatadt style=display:block>
	 <legend>Detail</legend>
	 
</select>

	<table>
	<tr>
	<td style='width:100px;'>Masukan Jam</td>
	<td style='width:100px;'>Masukan Menit</td>
	<td style='width:100px;'>Nilai Tekanan</td>
	<tr>
            <td>

				<select class='jam'><option selected='selected'>JAM</option>";
				for($a=0; $a<=23; $a+=1){
				echo"<option value=$a> $a </option>";
				} 
				echo"</select>
				</td>

				<td>
				<select class='menit'><option selected='selected'>MENIT</option>";
				 for($a=0; $a<=59; $a+=1){
				echo"<option value=$a> $a </option>";
				}
				echo"</select>
				</td>

				<td><input type=text class=tekanan size=8 onkeypress=\"return tanpa_kutip(event);\" class=myinputtex
            </td>        
    </tr>

	<tr>
		<td colspan=100>
				<input type=hidden id=methoddt value='insertdtl'>
				<button class=mybutton onclick=simpandtl()>" . $_SESSION['lang']['save'] . "</button>
				<button class=mybutton onclick=cancelGolongan()>" . $_SESSION['lang']['cancel'] . "</button>
			</td>
		</tr>

	</table>
	 </fieldset>";
	 echo "#####";// split string

	 	$str1 = "select * from " . $dbname . ".pabrik_tekananbolerdt where idht = '".$id_parent."'";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		echo open_theme($_SESSION['lang']['list']);
		echo"<table class=sortable cellspacing=1 cellpadding=5 border=0 id=listDatadt1 style=display:block style=min-width:500px; >
		     <thead>
			 <tr class=rowheader>
			 <td>No</td>
			 <td style='width:100px;'>" . $_SESSION['lang']['jam'] . "</td>
			 <td style='width:100px;'>Nilai Tekanan</td>
			 <td>Action</td>
			 </thead>
			 <tbody>";
		while ($bar1 = $res1->fetch()) {
			$no++;
		    echo"<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td align=center>" . $bar1->jam . "</td>
		    <td align=right>" . $bar1->nilaitekanan . "</td>
		    <td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('". $bar1->id ."');\">
                            </td>";		}
		echo "</tbody></table>";
		echo close_theme();
	 break;

case 'delete':
	$str = "delete from " . $dbname . ".pabrik_tekananbolerdt 
	where id='" . $id . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
		echo $id_parent;
        break;

	 case 'insertdtl':
	     if(count($jam) > 0){
	     	$str = "insert into " . $dbname . ".pabrik_tekananbolerdt (idht,jam,nilaitekanan) values ";
	     	$insertdata=array();
	   		for($i=0; $i<count($jam);$i++){

	   			$time= str_pad($jam[$i],2,'0',STR_PAD_LEFT).":".str_pad($menit[$i],2,'0',STR_PAD_LEFT).":00";
		    	$insertdata[] = "('" . $id_parent . "','" .  $time. "','" . $tekanan[$i] . "')";
			}

			$str .= implode(",",$insertdata);

	        try{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e){
				echo " Gagal," . addslashes($e->getMessage());
			}
	     } 
			echo $id_parent; 


        
        break;
    case 'loaddatadetail':
		
    break;
	case 'loaddata':
	 echo "Loaddata";

    				 
    break;
	
	case'deletedata':
		$str="delete from ".$dbname.".pabrik_tekananbolerht where id='".$id."'";
		try{$owlPDO->exec($str);}catch (PDOException $e){echo "Gagal : ".addslashes($e->getMessage());}
	break;

    default:
        break;
}

if ($toexcel == 'yes')
{
	$stream = $tab;
    $nop_ = "Analisa_Tekanan_Muatan" . date('Ymd_His');
    if (strlen($stream) > 0) {
        if ($handle = opendir('tempExcel')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != "index.html") {
                    @unlink('tempExcel/' . $file);
                }
            }
            closedir($handle);
        }
        $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
        if (!fwrite($handle, $stream)) {
            echo "<script language=javascript1.2>
                        parent.window.alert('Cant convert to excel format');
                        </script>";
            exit;
        } else {
            echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
        }
        closedir($handle);
    }
}else{
	echo $tab;

}

?>
