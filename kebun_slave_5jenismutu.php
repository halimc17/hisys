<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
	
$method=checkPostGet('method','');

$idjenis = checkPostGet('idjenis','');
$jenis = checkPostGet('jenis','');
$kriteria = checkPostGet('kriteria','');
$satuan = checkPostGet('satuan','');
$satuan99 = checkPostGet('satuan99','');
	
switch($method)
{
	case 'insert1':
		if($jenis==''||$kriteria==''||$satuan=='')
		{
			echo "Gagal : Semua field harus diisi.";
			exit();
		}
		
		$str = "insert into ".$dbname.".kebun_5jenismutu (jenis,kriteria,satuan,satuan2) values ('".$jenis."','".$kriteria."','".$satuan."','".$satuan99."')";
		try
		{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e)
		{
			echo "Gagal : ".$e->getMessage();
			die();
		}
	break;
	
	case 'update1':
		if($jenis==''||$kriteria==''||$satuan=='')
		{
			echo "Gagal : Semua field harus diisi.";
			exit();
		}
	
		$str="update ".$dbname.".kebun_5jenismutu set jenis='".$jenis."',kriteria='".$kriteria."',satuan='".$satuan."',satuan2='".$satuan99."' where idjenis='".$idjenis."' ";
		
		try
		{
			$owlPDO->exec($str);
		}
		catch (PDOException $e)
		{
			print " Gagal  : " . $e->getMessage() . "<br/>"; 
		    die(); 
		}
	break;
	
	case'loaddata1':
		$tab.="<table class=sortable cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['jenis']."</td>
				<td align=center>".$_SESSION['lang']['kriteria']."</td>
				<td align=center>".$_SESSION['lang']['satuan']."</td>
				<td align=center>".$_SESSION['lang']['satuan']." / ".$_SESSION['lang']['unit']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
            <tbody>";
			
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
			
            $str = "select count(*) as jmlhrow from ".$dbname.".kebun_5jenismutu";
            $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
			{  
                $jlhbrs= $bar->jmlhrow;
            }
			
            $no=$maxdisplay;
			
            $str="select * from ".$dbname.".kebun_5jenismutu order by jenis asc limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch())
			{
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=right>".$no."</td>";
				$tab.="<td align=left>".$bar['jenis']."</td>";
				$tab.="<td align=left>".$bar['kriteria']."</td>";
				$tab.="<td align=center>".$bar['satuan']."</td>";
				$tab.="<td align=center>".$bar['satuan2']."</td>";
                $tab.="<td align=center>
                        <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                        onclick=\"fillfield1('".$bar['jenis']."','".$bar['kriteria']."','".$bar['idjenis']."','".$bar['satuan']."','".$bar['satuan2']."')\">
						<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=hapus1('".$bar['idjenis']."');>
                      </td>";
                $tab.="</tr>";//
            }
			
            $tab.="
            <tr class=rowheader><td colspan=6 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadDatajenis(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadDatajenis(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
		echo $tab;
	break;
	case 'delete1':
		$str="delete from ".$dbname.".kebun_5jenismutu where idjenis='".$idjenis."'";
		try
		{
			$owlPDO->exec($str);
		}
		catch (PDOException $e) 
		{
			print " Gagal  : " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;	
		
		default:	
	}
?>