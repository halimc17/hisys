<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>

<script language=javascript1.2 src=js/vhc.js></script>
<?
include('master_mainMenu.php');
$path	= "fileupload/jenis_vhc/";
//get enum untuk kelompok vhc;
        $optklvhc="";
        $arrklvhc=getEnum($dbname,'vhc_5master','kelompokvhc');
        foreach($arrklvhc as $kei=>$fal)
        {
			switch($kei)
			{
			 case 'AB':
				  $_SESSION['language']!='EN'?$fal='Alat Berat':$fal='Heavy Equipment';
			 break;
			 case 'KD':                            
				 $_SESSION['language']!='EN'?$fal='Kendaraan':$fal='Vehicle';
			 break;
			 case 'MS':
				 $_SESSION['language']!='EN'? $fal='Mesin':$fal='Machinery';
			 break;
			}
			$optklvhc.="<option value='".$kei."'>".$fal."</option>";
        } 
OPEN_BOX('','<span class=judul>'.getMenu('vhc_5jenisvhc').'</span>');
echo"<fieldset style='width:500px;'><table border=0>
		 <tr>
			<td>".$_SESSION['lang']['kodekelompok']."</td>
			<td style='width:100px;'><select style='width:100px;' id=kelompokvhc>".$optklvhc."</select>
		 </td>
			<td align=right>  ".$_SESSION['lang']['tipe']."  </td>
			<td><input style='width:100px;' onkeydown=\"upperCaseF(this)\" type=text id=jenisvhc size=5 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=5></td>
		 </tr>
         <tr>
			<td>".$_SESSION['lang']['namajenisvhc']."</td>
			<td colspan=3><input style='width:233px;' type=text id=namajenisvhc size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=45></td>
		 </tr>
         <tr>
			<td>".$_SESSION['lang']['akunservice']."</td>
			<td><input style='width:98px;' type=text id=noakun size=16 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=16></td>
		 </tr>
		 <tr>
			<td >  ".$_SESSION['lang']['namafile']."  </td>
			<td colspan=3><input type=file id=upload class=mybutton></td>
		 </tr>
		 <tr>
		 <td><td colspan=3>
         <input type=hidden id=method value='insert'>
         <button class=mybutton onclick=simpanVhc()>".$_SESSION['lang']['save']."</button>
         
         <button class=mybutton onclick=cancelVhc()>".$_SESSION['lang']['cancel']."</button>
         </table></fieldset>";
		 
echo open_theme($_SESSION['lang']['availvhc']);
echo "<div>";
        $str1="select * from ".$dbname.".vhc_5jenisvhc order by kelompokvhc asc, jenisvhc asc";
        echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
             <thead>
                 <tr class=rowheader>
                   <td style='width:50px;'>".$_SESSION['lang']['kodekelompok']."</td>
                   <td style='width:50px;'>".$_SESSION['lang']['tipe']."</td>
                   <td>".$_SESSION['lang']['namajenisvhc']."</td>
                   <td style='width:50px;'>".$_SESSION['lang']['noakun']."</td>		   
                   <td >".$_SESSION['lang']['namafile']."</td>		   
                   <td >".$_SESSION['lang']['updateby']."</td>		   
                   <td style='width:30px;'>Action</td></tr>
                 </thead>
                 <tbody id=container>";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res1->fetch())
        {
                echo"<tr class=rowcontent>
                     <td align=center>".$bar1->kelompokvhc."</td>
					 <td align=center>".$bar1->jenisvhc."</td>
					 <td>".$bar1->namajenisvhc."</td>
					 <td align=center>".$bar1->noakun."</td>
					 <td align=center>".$bar1->file."</td>
					 <td align=center>".getNamaKaryawan($bar1->updateby)."</td>
					 <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->jenisvhc."','".$bar1->namajenisvhc."','".$bar1->noakun."','".$bar1->kelompokvhc."');\">
					 <img src=images/zoom.png class=resicon onclick=\"isifile('".$path.$bar1->file."','event');\" title='view'>
					 </td></tr>";
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