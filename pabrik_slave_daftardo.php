<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


$method=checkPostGet('method','');
$ket=checkPostGet('ket','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
//$pabrik=checkPostGet('pabrik','');
$nodaftar=checkPostGet('nodaftar','');
$nokontrak=checkPostGet('nokontrak','');
$nodo=checkPostGet('nodo','');
$komoditi=checkPostGet('komoditi','');
$volkontrak=checkPostGet('volkontrak','');
$toleransi=checkPostGet('toleransi','');
$cust=checkPostGet('cust','');
$pabrik=$_SESSION['empl']['lokasitugas'];


//exit("Error:$method");	
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmTangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan');


$nodaftarsch=checkPostGet('nodaftarsch','');
$nokontraksch=checkPostGet('nokontraksch','');
$nodosch=checkPostGet('nodosch','');
$tglsch=tanggalsystemn(checkPostGet('tglsch',''));
$komoditisch=checkPostGet('komoditisch','');

if($pabriksch==''){
	$pabriksch=$_SESSION['empl']['lokasitugas'];
}


if($tglsch=='--'){
    $tglsch='';
}

?>

<?php

switch($method)
{//'".$_SESSION['standard']['userid']."'
    
   case'getTangki':
        $optTangki.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $iTangki="select kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$pabrik."' and komoditi in ('CPO','KER') ";
        $nTangki=$owlPDO->query($iTangki) or die(print " Gagal: ".PDOException::getMessage());
        $nTangki->setFetchMode(PDO::FETCH_ASSOC);
        while($dTangki=$nTangki->fetch())
        {
            $optTangki.="<option value=".$dTangki['kodetangki'].">".$dTangki['keterangan']."</option>";
        }
        echo $optTangki;
    break;
    
   
    
    case 'insert':
		
		$date=str_replace('-','',$tgl);
		$notrantemp=$date.'/'.$pabrik.'/DAFTAR/';
			$str="select nodaftar from ".$dbname.".pabrik_blk_daftar where millcode='".$pabrik."' 
				and nodaftar like '".$notrantemp."%' order by nodaftar desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
				$noexpl=explode('/',$bar['nodaftar']);
				@$awal=$noexpl[3];
				@$awal=intval($awal);
				@$cektgl=$noexpl[0];
	
			if($date!=$cektgl){
				$awal=1;
			}else{
				$awal++;
			}
			$counter=addZero($awal,3);
			$nodaftar=$notrantemp.$counter;
	
		
        $str="INSERT INTO ".$dbname.".`pabrik_blk_daftar` 
				(`nodaftar`, `millcode`, `tanggal`, `nokontrak`, 
                `nodo`, `kodebarang`, `totalkontrak`, `toleransi`,
				`keterangan`,`updateby`,`namacustomer`)
        values ('".$nodaftar."','".$pabrik."','".$tgl."','".$nokontrak."',
				'".$nodo."','".$komoditi."','".$volkontrak."','".$toleransi."',
				'".$ket."','".$_SESSION['standard']['userid']."','".$cust."')";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;

    case 'update':
        $str="update ".$dbname.".pabrik_blk_daftar set 
				millcode='".$pabrik."',tanggal='".$tgl."',nokontrak='".$nokontrak."',
				nodo='".$nodo."',kodebarang='".$komoditi."',totalkontrak='".$volkontrak."',toleransi='".$toleransi."',
				keterangan='".$ket."',updateby='".$_SESSION['standard']['userid']."',namacustomer='".$cust."'		
				where nodaftar='".$nodaftar."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case 'delete':
        $str="delete from ".$dbname.".pabrik_blk_daftar where nodaftar='".$nodaftar."' ";
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
                    <table class=sortable cellspacing=1 border=0>
					 <thead>
						 <tr class=rowheader>
							<td align=center>".$_SESSION['lang']['nourut']."</td>
							<td align=center>".$_SESSION['lang']['notransaksi']."</td>
							<td align=center>".$_SESSION['lang']['tanggal']."</td>
							<td align=center>".$_SESSION['lang']['customer']."</td>  
							<td align=center>".$_SESSION['lang']['NoKontrak']."</td> 
							<td align=center>".$_SESSION['lang']['nodo']."</td>    
							<td align=center>".$_SESSION['lang']['komoditi']."</td>
							<td align=center>".$_SESSION['lang']['volumekontrak']."<br>(Kg)</td>
							<td align=center>".$_SESSION['lang']['toleransipenyusutan']."<br>(%)</td>
							<td align=center>".$_SESSION['lang']['keterangan']."</td>
							<td align=center>".$_SESSION['lang']['action']."</td></tr>
						 </tr>
                        </thead>
                        <tbody>";

			$where="";
			


		
			
			if($pabriksch!=''){
                $where.=" and millcode = '".$pabriksch."' ";
            }
            if($nodaftarsch!=''){
                $where.=" and nodaftar like '%".$nodaftarsch."%' ";
            }
			if($nokontraksch!=''){
                $where.=" and nokontrak like '%".$nokontraksch."%' ";
            }
			if($nodosch!=''){
                $where.=" and nodo like '%".$nodosch."%' ";
            }
			if($tglsch!=''){
                $where.=" and tanggal='".$tglsch."' ";
            }
			if($komoditisch!=''){
                $where.=" and kodebarang='".$komoditisch."' ";
            }
			
			
			
            $limit=10;
            $page=0;
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);

            $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_blk_daftar where 1=1 ".$where."  ";// echo $ql2;notran
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $str="select * from ".$dbname.".pabrik_blk_daftar where 1=1 ".$where." limit ".$offset.",".$limit."";
            //echo $iList;
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
                echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
				echo "<td align=left>".$bar['nodaftar']."</td>";
				echo "<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
				echo "<td align=left>".$bar['namacustomer']."</td>";
				echo "<td align=left>".$bar['nokontrak']."</td>";
				echo "<td align=left>".$bar['nodo']."</td>";
				echo "<td align=left>".$nmBrg[$bar['kodebarang']]."</td>";
				echo "<td align=right>".number_format($bar['totalkontrak'])."</td>";
				echo "<td align=right>".number_format($bar['toleransi'])."</td>";
				echo "<td align=left>".$bar['keterangan']."</td>";
                echo "<td align=center>
                        <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                        onclick=\"fillField('".$bar['nodaftar']."','".tanggalnormal($bar['tanggal'])."','".$bar['nokontrak']."','".$bar['nodo']."',
						'".$bar['kodebarang']."','".$bar['totalkontrak']."','".$bar['toleransi']."','".$bar['keterangan']."','".$bar['namacustomer']."');\">
                        
						<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['nodaftar']."');\">
                        </td>";
                echo "</tr>";//
            }
            echo"
            <tr class=rowheader><td colspan=11 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
    break;        
    case'getSounding':
        $sSound="select kuantitas,kernelquantity from ".$dbname.".pabrik_masukkeluartangki 
                 where tanggal='".$tgl."' and kodetangki='".$tangki."' and kodeorg='".$pabrik."'";
        $res=fetchData($sSound);

        if($res[0]['kuantitas']!=0){
            echo $res[0]['kuantitas'];
        }else{
            echo $res[0]['kernelquantity'];
        }
    break;
    

	
	
default:
}
?>