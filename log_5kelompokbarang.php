<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
	<script language=javascript src='js/kelompok_barang.js?v=<?php echo time(); ?>'></script>
	<link rel=stylesheet type='text/css' href='style/generic.css'>
<?
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul>' . getMenu('log_5kelompokbarang') . '</span><br>');

$jnsapp = "KL";
$str="select distinct kelompokbiaya from ".$dbname.".keu_5komponenbiaya order by kelompokbiaya";
$opt="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
while($bar=$res->fetch()){
	if($bar->kelompokbiaya==''){  
	}else{	  
		$opt.="<option value='".$bar->kelompokbiaya."'>".$bar->kelompokbiaya."</option>";
	}
}

// Option Akun
// $optAkun=array(""=>$_SESSION['lang']['pilihdata']);
// $optAkun+= makeOption($dbname,'keu_5akun','noakun,namaakun',"(noakun like '11501%' or noakun like '211%') and length(noakun)='7'",'2');

$optAkun = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT noakun, namaakun FROM ".$dbname.".keu_5akun WHERE (noakun like '11501%' or noakun like '211%') and length(noakun)='7' ORDER BY noakun ASC";
$res = fetchdata($str);
foreach($res as $val){
	$d=substr($val['noakun'],0,5);
	if($d!=$n){			
		$optAkun.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
	}
	$optAkun .= "<option value='".$val['noakun']."'>".$val['noakun']." - ".$val['namaakun']."</option>";
	$n=$d;
	if($d!=$n){			
		$optAkun.="</optgroup>";
	}
}

$optakungit = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT noakun, namaakun FROM ".$dbname.".keu_5akun WHERE (noakun like '11504%' or noakun like '211%') and length(noakun)='7' ORDER BY noakun ASC";
$res = fetchdata($str);
foreach($res as $val){
	$d=substr($val['noakun'],0,5);
	if($d!=$n){			
		$optakungit.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
	}
	$optakungit .= "<option value='".$val['noakun']."'>".$val['noakun']." - ".$val['namaakun']."</option>";
	$n=$d;
	if($d!=$n){			
		$optakungit.="</optgroup>";
	}
}


echo"<fieldset style=float:left>
     <legend>".$_SESSION['lang']['form']."</legend>
         <table>
         <tr>
           <td class='bintang'>".$_SESSION['lang']['materialgroupcode']."</td>
           <td>:</td>
           <td><input type=text class=myinputtext id=kelnumber style=width:285px; maxlength=3 onkeypress=\"return angka_doang(event);\"></td>

           <td>".$_SESSION['lang']['kelompokbiaya']."</td>
		   <td>:</td>
           <td><select style=width:287px id=kelompokbiaya>".$opt."</select></td>
		   
         </tr>
         <tr>
           <td class='bintang'>".$_SESSION['lang']['namakelompok']." (ID)</td>
		   <td>:</td>
           <td><input type=text class=myinputtext id=kelname style=width:285px; onkeypress=\"return tanpa_kutip(event);\"></td>
        
           <td class='bintang'>".$_SESSION['lang']['namakelompok']." (EN)</td>
		   <td>:</td>
           <td><input type=text class=myinputtext id=kelname1 style=width:285px; maxlength=60 onkeypress=\"return tanpa_kutip(event);\"></td>
         </tr>
        <tr>
           <td class='bintang'>".$_SESSION['lang']['noakun']."</td>
		   <td>:</td>
		   <td>
              <select id=noakun style=width:287px>
                ".$optAkun."
              </select>
            </td>
        
            <td style='display:none' class='bintang'>".$_SESSION['lang']['noakun']." (GIT)</td>
			<td style='display:none'>:</td>
            <td style='display:none'>
              <select id=noakungit style=width:287px>
                ".$optakungit."
              </select>
            </td>
         </tr>
         <tr>
            <td class='bintang'>".$_SESSION['lang']['status']."</td>
			<td>:</td>
            <td>
              <select id='status'>
                <option value='0'>Non-Aktif</option>
                <option value='1'>Aktif</option>
              </select>
            </td>
         </tr>
		<tbody id='trapproval'>";
		## APPROVAL ##
		$countApp = getCountApproval($jnsapp);
		for($i=1;$i<=$countApp;$i++)
		{
			$optApp="";
			$arrlistapp = listApprove($i,$jnsapp);
			foreach($arrlistapp as $key=>$val)
			{
				$optApp.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
			}
			echo"<tr>
				<td class='bintang'>".$_SESSION['lang']['persetujuan']." ".$i."</td>
				<td>:</td>
				<td>
					<select id='persetujuan".$i."'>".$optApp."</select>
				</td>
			</tr>";
		}
        echo"</tbody><tr><td></td><td></td><td>
         <input type=hidden value=insert id=method>
         <button class=mybutton onclick=saveKelompokBarang()>".$_SESSION['lang']['save']."</button>
         <button class=mybutton onclick=cancelKelompokBarang()>".$_SESSION['lang']['cancel']."</button>
		 </table>
     </fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"<div class='table-scroll' style=height:50vh>
	 <table class=sortable cellspacing=1 cellpadding=5 border=0>
     <thead>
          <tr class=rowheader>
           <th align=center>No</th>
           <th align=center width=50px>".str_replace("."," ",$_SESSION['lang']['materialgroupcode'])."</th>
           <th align=center>".$_SESSION['lang']['namakelompok']."</th>
           <th align=center>".$_SESSION['lang']['noakun']."</th>
           <th  style='display:none' align=center>".$_SESSION['lang']['noakun']." GIT</th>
           <th align=center>".$_SESSION['lang']['status']."</th>";
			for($i=1;$i<=$countApp;$i++)
			{
				echo"<th align=center>".$_SESSION['lang']['persetujuan']." ".$i."</th>";
			}
        echo"<th align=center>Action</th>
          </tr>
         </thead>
         <tbody id=container>";
$no=0;	 
$str="select * from ".$dbname.".log_5klbarang order by kode asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
while($bar=$res->fetch())
{
  $no+=1;	
  echo"<tr class=rowcontent>
           <td align=center>".$no."</td>
           <td align=center>".$bar->kode."</td>
           <td>(ID) ".$bar->kelompok."<br>
			(EN) ".$bar->kelompok1."</td>
           <td>".$bar->noakun." - ".getNamaAkun($bar->noakun)."</td>
           <td style='display:none'>".$bar->noakungit." - ".getNamaAkun($bar->noakungit)."</td>
           <td align=center>".($bar->status=='0' ? 'Non-Aktif' : ($bar->status=='3' ? 'Ditolak' : 'Aktif'))."</td>";
			## APPROVAL ##
			$countApp = getCountApproval($jnsapp);
			for($i=1;$i<=$countApp;$i++)
			{
				$arrdetail = detailApprove($i,$bar->kode,$jnsapp);
				
				echo"<td align=center>".$arrdetail['nama']."<br>(".($arrdetail['status']=='0'?'Menunggu Keputusan':($arrdetail['status']=='3'?'Ditolak':'Disetujui')).")</td>";
			}
		
		echo"<td align=center>";		
			if($bar->status=='1'){
				echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kode."','".$bar->kelompok."','".$bar->kelompok1."','".$bar->kelompokbiaya."','".$bar->noakun."','".$bar->noakungit."');\">&nbsp;";
			}
			else if($bar->status=='3'){
        echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kode."','".$bar->kelompok."','".$bar->kelompok1."','".$bar->kelompokbiaya."','".$bar->noakun."','".$bar->noakungit."');\">&nbsp;";
				echo"<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delKelompok('".$bar->kode."','".$bar->kelompok."');\">";
			}
			else{}
		echo"</td>
       </tr>";	
}     
echo"</tbody>
     <tfoot>
         </tfoot>
         </table></div>";
CLOSE_BOX();
echo close_body();
?>