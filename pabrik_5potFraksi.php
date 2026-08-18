<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2>
    function simpanJabatan()
    {
        kode=document.getElementById('kode').value;
        potongan=document.getElementById('potongan').value;
        if(kode=='' || potongan=='')
            alert('Fields are oblogatory');
        else
           {
               param='kode='+kode+'&potongan='+potongan;
                tujuan = 'pabrik_slave_save_pot_sortasi.php';
		post_response_text(tujuan, param, respog);
				cancelJabatan();
           } 
			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}   
    }

function cancelJabatan()
{
         document.getElementById('kode').value='';
         document.getElementById('potongan').value='';
         document.getElementById('kode').disabled=false;
}

function fillField(x,y)
{
         document.getElementById('kode').value=x;
         document.getElementById('potongan').value=y;   
         document.getElementById('kode').disabled=true;
}
</script>
<?
include('master_mainMenu.php');

$str="select kode,keterangan from ".$dbname.".pabrik_5fraksi2  order by kode asc";
$optOrg="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optOrg.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->keterangan."</option>";
}


OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5potFraksi').'</span>');
echo"<fieldset style='width:500px;'><table>
     <tr><td>".$_SESSION['lang']['kodeabs']."</td><td> : </td>
		 <td><select style='width:100px;' id=kode>".$optOrg."</select></td></tr>
		 
	 <tr><td>".$_SESSION['lang']['potongan']."</td><td> : </td>
	 <td><input style='width:96px;' type=text id=potongan size=4 onkeypress=\"return angka_doang(event);\" class=myinputtext></td></tr>
 	 <tr><td><td><td>
	 <button class=mybutton onclick=simpanJabatan()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelJabatan()>".$_SESSION['lang']['cancel']."</button>
	 </td></td></td></tr></table></fieldset>";
echo open_theme($_SESSION['lang']['list']);
echo "<div>";
	
	echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
			<td align=center style='width:50px;'>".$_SESSION['lang']['kodeabs']."</td>
			<td align=center >".$_SESSION['lang']['nama']."</td>
			<td align=center >".$_SESSION['lang']['nama']." (EN)</td>
			<td align=center >".$_SESSION['lang']['potongan']."</td>
			<td align=center >".$_SESSION['lang']['updateby']."</td>
			<td  align=center style='width:30px;'>Action</td></tr>
		 </thead>
		 <tbody id=container>";
        $str1="select a.*,b.keterangan, b.keterangan1 from ".$dbname.".pabrik_5pot_fraksi a LEFT JOIN
		".$dbname.".pabrik_5fraksi2 b ON a.kodefraksi = b.kode
		order by a.kodefraksi";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch())
	{
		$updateby   = $bar1->createby;
		if($bar1->updateby == '0000000000'){
			$updateby = $bar1->createby;
		}
		$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$updateby."'");
		echo"<tr class=rowcontent><td align=center>".$bar1->kodefraksi."</td>
			<td>".$bar1->keterangan."</td>
			<td>".$bar1->keterangan1."</td>
			<td align=right>".$bar1->potongan."</td>
			<td align=center>".$nmKar[$updateby]."</td>
			<td align=center ><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodefraksi."','".$bar1->potongan."');\"></td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>