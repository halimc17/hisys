<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kd_bag=$_POST['rkd_bag'];
$jumlah=$_POST['jumlah'];
$kdvhc=$_POST['kdvhc'];
// if((isset($_POST['txtfind3']))!=''){
    $txtfind=$_POST['txtfind3'];
	if($kd_bag!=''){
		$wh=" and kodetraksi like '".$kd_bag."%'";
	}
	if($jumlah==''){
		exit("Warning: Isikan jumlah diminta terlebih dahulu.");
	}
	
	
    $str="select * from ".$dbname.".vhc_5master where (kodevhc like '%".$txtfind."%' or nopol like '%".$txtfind."%') ".$wh.""; //exit('error'.$str);
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
        echo"
        <table class=sortable cellspacing=1 cellpadding=5  border=0>
            <thead>
                <tr class=rowheader>
                    <th>No.</th>
                    <th>".$_SESSION['lang']['kodevhc']."</th>
                    <th>".$_SESSION['lang']['nopol']."</th>
                    <th>".$_SESSION['lang']['kodeorg']."</th>
                    <th>".$_SESSION['lang']['traksi']."</th>
                    <th>#</th>
                    <th>".$_SESSION['lang']['jumlah']."</th>
					</tr>
                    </thead>
                    <tbody>";
                    while($bar=$res->fetch()){
						$no++;
                        echo"<tr class=rowcontent style='cursor:pointer;'  >
                            <td align=center>".$no."</td>
                            <td name=nama[]>".$bar->kodevhc."</td>";
							if($bar->nopol!=''){								
								echo"<td>".$bar->nopol."</td>";
							}else{
								echo"<td>".$bar->detailvhc."</td>";
							}
						$check="";	
						$value="";
						$disabled="disabled";						
						$tempvhc=explode(",",$kdvhc);
						foreach($tempvhc as $kode){
							$vhc=explode("=",$kode);
							if($vhc[0]==$bar->kodevhc){								
								$check="checked";
								$value=$vhc[1];								
								$disabled="";						
							}
						}
                        echo"<td>".getNamaOrg($bar->kodeorg)."</td>
                            <td>".getNamaOrg($bar->kodetraksi)."</td>
                            <td align=center><input ".$check." name=check[] id=check_".$no." type=checkbox onclick=getjumlahpervhc('".$no."');></td>
                            <td><input style=width:50px ".$disabled." class=myinputtextnumber name=jlhpervhc[] id=jlhpervhc".$no." value=".$value."></td>
                            </tr>";
                    }
			echo "</tbody>
				<tfoot>
					<tr>
						<td colspan=7><button onclick=\"setVhc()\" title='Click' class=mybutton>Simpan</button></td>
					</tr>
				</tfoot>
	</table>";
					
// }
?>