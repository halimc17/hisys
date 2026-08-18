<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$kodeorg=checkPostGet('kodeorg','');
$divisi=checkPostGet('divisi','');
$blok=checkPostGet('blok','');
$seksi=checkPostGet('seksi','');
echo $tahuntanam=checkPostGet('tahuntanam','');

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmTangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan');

switch($method){   
    
    case 'insert':
		
		$str="INSERT INTO ".$dbname.".`kebun_5seksipanen` 
				(`divisi`, `blok`, `tahuntanam`, `seksi`,`updateby`)
        values ('".$divisi."','".$blok."','".$tahuntanam."','".$seksi."','".$_SESSION['standard']['userid']."')";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case 'delete':
        $str="delete from ".$dbname.".kebun_5seksipanen where blok='".$blok."' and seksi='".$seksi."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
        

    case'loadData':
            echo"<div id=container>
                    <table class=sortable cellspacing=1 border=0 width=310px>
					 <thead>
						 <tr class=rowheader>
							<td align=center>No</td>
							<td align=center>".$_SESSION['lang']['divisi']."</td>
							<td align=center>".$_SESSION['lang']['blok']."</td>
							<td align=center>".$_SESSION['lang']['tahuntanam']."</td>
							<td align=center>".$_SESSION['lang']['seksi']."</td> 
							<td align=center>".$_SESSION['lang']['action']."</td></tr>
						 </tr>
                        </thead>
                        <tbody>";

			$where="";
				
			
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

            $ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5seksipanen";
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $str="select * from ".$dbname.".kebun_5seksipanen where '".$_SESSION['empl']['lokasitugas']."'  = substr(blok,1,4) limit ".$offset.",".$limit."";
			
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
                echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
				echo "<td align=left>".substr($bar['blok'],0,6)."</td>";
				echo "<td align=left>".$bar['blok']."</td>";
				echo "<td align=left>".$bar['tahuntanam']."</td>";
				echo "<td align=left>".$bar['seksi']."</td>";
                echo "<td align=center>
						<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['blok']."','".$bar['seksi']."');\">
                        </td>";
                echo "</tr>";//
            }
            echo"
            <tr class=rowheader><td colspan=6 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
    break;
	
	case 'getdivisi':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorg."%' and tipe ='AFDELING' order by kodeorganisasi asc";
		$optdivisi.="<option value=''></option>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}		
			echo $optdivisi;
			break;
	case 'getblok':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$divisi."%' and tipe ='BLOK' order by kodeorganisasi asc";
		// exit ('error :' .$str);
		$optblok.="<option value=''></option>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optblok.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}		
			echo $optblok;
			break;
	case 'gettahuntanam':
		$str="select kodeorg, tahuntanam, statusblok from ".$dbname.".setup_blok where kodeorg = '".$blok."'";
		// exit ('error :' .$str);
		$tahuntanam.="";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$tahuntanam = $bar['tahuntanam'];
				$statusblok = $bar['statusblok'];
			}		
			echo $tahuntanam." - ".$statusblok;
			break;
default:
}
?>