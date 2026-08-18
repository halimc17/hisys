<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');



# Get Attr

$nourut=checkPostGet('nourut','');
$nourutparent=checkPostGet('nourutparent','');
$namapenghasilan=checkPostGet('namapenghasilan','');
$kodepajak=checkPostGet('kodepajak','');
$idd=checkPostGet('kodepenghasilan','');
$iddt=checkPostGet('kodepenghasilandt','');
$idparent=checkPostGet('idparent','');
$idparentdt=checkPostGet('idparentdt','');
$idpenghasilan=checkPostGet('idpenghasilan','');
$proses=checkPostGet('proses','');
$page=checkPostGet('page','');

switch($proses) {
    
   #= case loaddata
	case'loaddata':
		$limit=10;
		$offset=$page*$limit;
		$maxdisplay=($page*$limit);

		$str="select count(*) as jmlhrow from ".$dbname.".pmn_5jenispenghasilan where idparent='0' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jlhbrs= $bar['jmlhrow'];
		
		$no=$maxdisplay;	
		$str="select * from ".$dbname.".pmn_5jenispenghasilan a left join ".$dbname.".keu_5akun b 
		on a.kodepajak=b.noakun where  idparent='0' limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			//exit('error'.$bar->namapenghasilan);
			//echo $no;
			@$no+=1;
			echo"<tr class=rowcontent>";
			echo"<td align=center>".$no."</td>";
			echo"<td align=left>".$bar['namapenghasilan']."</td>";
			echo"<td align=right>".$bar['kodepajak']."</td>";
			echo"<td align=left>".$bar['namaakun']."</td>";
			echo"<td align=right>".$bar['nourutparent']."</td>";
			echo"<td><img src='images/application/application_edit.png' class=resicon caption='Edit' 
					onclick=\"fillField('".$bar['idpenghasilan']."','".$bar['kodepajak']."','".$bar['namapenghasilan']."','".$bar['nourutparent']."');\">";
			echo"<td><img src=images/onebit_02.png class=resicon  title='detail' onclick=\"detailpenghasilan('".$bar['idpenghasilan']."','".$bar['kodepajak']."','".$bar['namapenghasilan']."');\"></td>";			
			echo"</tr>";	 
		}
		echo"
            <tr class=rowheader><td colspan=12 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
            echo"</tbody>";
		break;
		
	#= case insert ht
	case'insert':
		#= cara baru menentukan maxid
		$str="select count(*) as max from ".$dbname.".pmn_5jenispenghasilan ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			
			#= cara 1
			$max= $bar['max'];
			$idbaru=$max+1;
			
			#= cara 2
			//$idbaru=$bar['max']+1;
			
		#= buat query insertnya
		$str = "insert into " . $dbname . ".pmn_5jenispenghasilan (idpenghasilan,idparent,nourutparent,nourutchild,namapenghasilan,kodepajak)
          values('" . $idbaru . "','".$idparent."','" . $nourutparent . "','" . $nourutchild . "','" . $namapenghasilan . "','" . $kodepajak . "')";
        //exit('error'.$idbaru);
        try{
            $owlPDO->exec($str); 
        }
        catch (PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }	
			
	
	break;
   
	#= insert dt
	case 'insertdt':
	
	$str="select count(*) as max from ".$dbname.".pmn_5jenispenghasilan ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$max= $bar['max'];
			$idbaru=$max+1;

		#= buat query insertnya
		$str = "insert into " . $dbname . ".pmn_5jenispenghasilan (idpenghasilan,idparent,nourutchild,namapenghasilan,kodepajak)
          values('" . $idbaru . "','".$idparentdt."','" . $nourut . "','" . $namapenghasilan . "','" . $kodepajak . "')";
        try{
            $owlPDO->exec($str); 
        }
        catch (PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }	


	break;

	case 'update':

        $str = "update " . $dbname . ".pmn_5jenispenghasilan set nourutparent='".$nourut."',namapenghasilan='".$namapenghasilan."',kodepajak='" . $kodepajak. "'
           where idpenghasilan='" . $idd . "'";
        try{
            $owlPDO->exec($str); 

        }
        catch (PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        break;

	case 'updatedt':

        $str = "update " . $dbname . ".pmn_5jenispenghasilan set 
				namapenghasilan='".$namapenghasilan."',nourutchild='".$nourut."' where idpenghasilan='" . $iddt . "'";
        try{
            $owlPDO->exec($str); 

        }
        catch (PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
        
	break;
          

	case'loaddetail':	
	   echo" <fieldset  style='width:500px;'>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table>
		<tr hidden>
			<td>Kode Pajak</td>
			<td>:</td>
			<td>
				<input type=text id=kodepajakdt value='".$kodepajak."' maxlength=80 style=width:150px disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext>
			</td>
		</tr>
		
		<tr hidden>
			<td>Kode Penghasilan </td>
			<td>:</td>
			<td><input type=text id=kodepenghasilandt maxlength=80 style=width:150px disabled onkeypress=\"return tanpa_kutip(event);\" class=myinputtext ></td>
		</tr>
		
		<tr hidden>
			<td>ID Parent</td>
			<td>:</td>
			<td><input type=text id=idparentdt value='".$idpenghasilan."' maxlength=2 style=width:150px  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber disabled></td>
		</tr>
		
		<tr>
			<td>No Urut</td>
			<td>:</td>
			<td><input type=text id=nourutdt maxlength=2 style=width:150px  onkeypress=\"return angka_doang(event);\" class=myinputtextnumber></td>
		</tr>
		<tr>
			<td>Nama Penghasilan</td>
			<td>:</td>
			<td><input type=text id=namapenghasilandt maxlength=80 style=width:250px onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		</tr>	
		<tr>
			<td colspan=2></td>
			<td>	
			 <button class=mybutton onclick=simpandt()>".$_SESSION['lang']['save']."</button>
			 <button class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button></td>
		</tr>
		 <input type=hidden id=prosesdt value='insertdt'>
	 </table>
	 </fieldset>";
      
       echo" <fieldset style='width:500px;'>
			<legend>".$_SESSION['lang']['list']." ".$_SESSION['lang']['']."</legend>
	
			<table class=sortable cellspacing=1 cellpadding=3 border=0>
				<thead>
					<tr class=rowheader>
						<td align=center>".$_SESSION['lang']['nourut']."</td>
						<td align=center>Nama<br>Penghasilan</td>
						<td align=center>".$_SESSION['lang']['noakun']."</td>
						<td align=center>".$_SESSION['lang']['namaakun']."</td>
						<td align=center>".$_SESSION['lang']['nourut']."<br>".$_SESSION['lang']['laporan']."</td>
							<td colspan=2 style=text-align:center;>".$_SESSION['lang']['action']."</td>
						
					</tr>
				</thead>
		";
	
	
		
		$str="select * from ".$dbname.".pmn_5jenispenghasilan a left join ".$dbname.".keu_5akun b 
		on a.kodepajak=b.noakun where  idparent=".$idpenghasilan."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$nodt+=1;
			echo"<tr class=rowcontent>";
			echo"<td align=left>".$nodt."</td>";
			echo"<td align=left>".$bar['namapenghasilan']."</td>";
			echo"<td align=right>".$bar['kodepajak']."</td>";
			echo"<td align=left>".$bar['namaakun']."</td>";
			echo"<td align=left>".$bar['nourutchild']."</td>";
			echo"<td> <img src='images/application/application_edit.png' class=resicon caption='Edit' 
					onclick=\"fillFielddt('" . $bar['idpenghasilan'] . "','" . $bar['nourutchild'] . "','" . $bar['namapenghasilan'] . "');\"></td>";			
			echo"</tr>";	 
		}
		echo"</table></fieldset>";
	
    break;
	
	
	
     default:
        break;
    }
?>