<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
echo open_body();
?>

<script language=javascript1.2 src='js/datakaryawan.js'></script>
<script type='text/javascript' language='javascript' src='js/rasterizeHTML.allinone.js'></script>

<?php		
//MASUK
//thn
$optthnmsk="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql="SELECT distinct left(tanggalmasuk,4) as thnmsk FROM ".$dbname.".datakaryawan order by tanggalmasuk desc";
$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$res->fetch())
    {
            $optthnmsk.="<option value=".$data['thnmsk'].">".$data['thnmsk']."</option>";
    }		
//bln
$optblnmsk="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "SELECT distinct mid(tanggalmasuk,6,2) as blnmsk FROM ".$dbname.".datakaryawan order by mid(tanggalmasuk,6,2) desc";
$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$res->fetch())
    {
            $optblnmsk.="<option value=".$data['blnmsk'].">".$data['blnmsk']."</option>";
    }

//KELUAR
//thn
$optthnkel="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql="SELECT distinct left(tanggalkeluar,4) as thnkel FROM ".$dbname.".datakaryawan order by tanggalkeluar desc";
$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$res->fetch())
        {
                $optthnkel.="<option value=".$data['thnkel'].">".$data['thnkel']."</option>";
        }		
//bln
$optblnkel="<option value=''>".$_SESSION['lang']['all']."</option>";
$sql = "SELECT distinct mid(tanggalkeluar,6,2) as blnkel FROM ".$dbname.".datakaryawan order by mid(tanggalkeluar,6,2) desc";
$res=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$res->fetch())
    {
            $optblnkel.="<option value=".$data['blnkel'].">".$data['blnkel']."</option>";
    }

include('master_mainMenu.php');


OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['inputdatakaryawan']).'</span>');
$optpt='';
$optlokasitugas='';
//if(user is under holding)
$saveable='';
$str="select 1=1";
// if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')//user holding dapat menempatkan dimana saja
// {
//         $strPt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi   where tipe='PT' order by namaorganisasi asc";
//         $res=$owlPDO->query($strPt) or die(print " Gagal: ".PDOException::getMessage());
//         $res->setFetchMode(PDO::FETCH_OBJ);
//         while($barPt=$res->fetch())
//         {
//                         $optpt.="<option value='".$barPt->kodeorganisasi."'>".$barPt->namaorganisasi."</option>";	
//         }
        
//         $str="select distinct a.lokasitugas as kodeorganisasi,namaorganisasi from ".$dbname.".datakaryawan a 
//               left join ".$dbname.".organisasi b on a.lokasitugas=b.kodeorganisasi order by namaorganisasi asc";
//         $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//         $res->setFetchMode(PDO::FETCH_OBJ);
//         while($bar=$res->fetch())
//         {
//                         $optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
//         }

// }
// else if(trim($_SESSION['empl']['tipelokasitugas'])!='HOLDING')
// {
//     $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi "
//             . " where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe !='HOLDING' "
//             . "  order by namaorganisasi asc";
//     $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
//     $res->setFetchMode(PDO::FETCH_OBJ);
//     while($bar=$res->fetch())
//     {
//         $optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
//     }
// }
// else
// {
//   $saveable='disabled';
//   echo"<script>
//         alert('You are not authorized');
//        </script>";
// }

if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')//user holding dapat menempatkan dimana saja
{
    $strPt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi   where tipe='PT' order by namaorganisasi asc";
        $res=$owlPDO->query($strPt) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($barPt=$res->fetch())
        {
                        $optpt.="<option value='".$barPt->kodeorganisasi."'>".$barPt->namaorganisasi."</option>";    
        }

    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION') 
              and length(kodeorganisasi)=4 order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    $optAll='';
    while($bar=$res->fetch())
    {
        $optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";   
    }
       
}
else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL')
{
    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe not in('BLOK','PT','STENGINE','STATION')
                   and length(kodeorganisasi)=4
          and (kodeorganisasi in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional != 'JAKARTA')
          or induk in (select distinct kodeunit from ".$dbname.".bgt_regional_assignment where regional != 'JAKARTA')) 
          order by kodeorganisasi asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";    
        }
    
}else if(trim($_SESSION['org']['induk']!=''))//user unit hanya dapat menempatkan pada unitnya dan anak unitnya
{

    $str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)=4 
        and kodeorganisasi  like '".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi";
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$res->fetch()){
        $optlokasitugas.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";   
    }
       
}else{
  $saveable='disabled';
  echo"<script>
        alert('You are not authorized');
       </script>";
}

//Tipe karyawan
$opttipekaryawan='';
if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')//jika user holding dapat memunculkan pilihan Permanen(staff))
      $str="select * from ".$dbname.".sdm_5tipekaryawan order by tipe";
else//pilihan staff dihilangkan, input data staff hanya dari pusat
      $str="select * from ".$dbname.".sdm_5tipekaryawan where id<>0 order by tipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
      $opttipekaryawan.="<option value='".$bar->id."'>".$bar->tipe."</option>";	
}	
$opttipekaryawan.="<option value=100>Kecuali BHL/KHL</option>";	

//===========get jeniskeamin enum
//get Golongan darah from enum
$optJK="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrenum=getEnum($dbname,'datakaryawan','jeniskelamin');
foreach($arrenum as $key=>$val)
{

        $optJK.="<option value='".$key."'>".$val."</option>";
}

echo"<canvas id='myCanvas' width='400' height='200' style='display:none;background-color:#FFFFFF;'></canvas><table border=0>
     <tr valign=middle>
         <td><fieldset style='width:1050px;'><legend>".$_SESSION['lang']['find']."</legend>"; 
if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
        echo $_SESSION['lang']['perusahaan']." : <select id=schpt style='width:150px' onchange='filterlokasitugas()'><option value=''>".$_SESSION['lang']['all']."</option>".$optpt."</select><p />";
}else{
        echo "<select id=schpt style='width:150px;display:none' onchange='filterlokasitugas()'><option value=''>".$_SESSION['lang']['all']."</option></select>";
}

echo" ".$_SESSION['lang']['caripadanama']." : <input type=text id=txtsearch  style='width:120px' size=25 maxlength=30 class=myinputtext> &nbsp";

echo $_SESSION['lang']['lokasitugas']." : <select id=schorg  style='width:120px' onchange=changeCaption(this.options[this.selectedIndex].text);><option value=''>".$_SESSION['lang']['all']."</option>".$optlokasitugas."</select> &nbsp ";

echo $_SESSION['lang']['tipekaryawan']." : <select style='width:120px' id=schtipe  onchange=changeCaption1(this.options[this.selectedIndex].text);><option value=''>".$_SESSION['lang']['all']."</option>".$opttipekaryawan."</select> &nbsp ";

echo $_SESSION['lang']['status']." : <select id=schstatus  style='width:75px' onchange=changeCaption(this.options[this.selectedIndex].text);><option value=''>".$_SESSION['lang']['all']."</option><option value='0000-00-00'>".$_SESSION['lang']['aktif']."</option><option value='*'>".$_SESSION['lang']['tidakaktif']."</select> &nbsp ";

echo $_SESSION['lang']['jeniskelamin']." : <select id=schjk  style='width:75px' onchange=changeCaption(this.options[this.selectedIndex].text);>".$optJK."</select> &nbsp ";

//echo "<td align=right>".$_SESSION['lang']['jeniskelamin']."</td><td><select id=jeniskelamin  style='width:150px;'>".$optJK."</select></td>";

echo"<button class=mybutton onclick=cariKaryawanLaporan(1)>".$_SESSION['lang']['find']."</button>";
echo"</fieldset></td>
     </tr>
         </table> "; 

echo"<table>
     <tr valign=middle>
         <td><fieldset style='width:1050px;'><legend>Find by period</legend>"; 


echo"".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['masuk']." : <select id='thnmsk' style='width:150px;' onchange=changeCaption1(this.options[this.selectedIndex].text);>".$optthnmsk." &nbsp  </select>";

echo" ".$_SESSION['lang']['bulan']." ".$_SESSION['lang']['masuk']." : <select id='blnmsk' style='width:150px;' onchange=changeCaption1(this.options[this.selectedIndex].text);>".$optblnmsk." &nbsp  </select>";

echo"".$_SESSION['lang']['tahun']." ".$_SESSION['lang']['keluar']." : <select id='thnkel' style='width:150px;' onchange=changeCaption1(this.options[this.selectedIndex].text);>".$optthnkel." &nbsp  </select>";

echo" ".$_SESSION['lang']['bulan']." ".$_SESSION['lang']['keluar']." : <select id='blnkel' style='width:150px;' onchange=changeCaption1(this.options[this.selectedIndex].text);>".$optblnkel."</select>&nbsp &nbsp";

echo"<button class=mybutton onclick=cariKaryawanLaporan(1)>".$_SESSION['lang']['find']."</button> ";
echo"</fieldset></td>
     </tr>
         </table> "; 

echo"<div id='searchplace'><fieldset><legend>".$_SESSION['lang']['result']."</legend>".$_SESSION['lang']['daftarkaryawan']." : <span id=cap1></span> <span id=cap2></span>";
echo" <img src=images/excel.jpg class=resicon title='Excel' onclick=datakaryawanExcel(event,'".(isset($thnm) ? $thnm : "")."','".(isset($blnm) ? $blnm : "")."','".(isset($thnk) ? $thnk : "")."','".(isset($blnk) ? $blnk : "")."','sdm_slave_datakaryawan_Excel.php')>
         <table class=sortable border=0 cellspacing=1 width=100%>
         <thead>
           <tr class=rowheader>
             <td align=center>".substr($_SESSION['lang']['nomor'],0,2)."</td>
                 <td align=center>".$_SESSION['lang']['nik']."</td>
                 <td align=center>".$_SESSION['lang']['nama']."</td>
                 <td align=center>".$_SESSION['lang']['functionname']."</td>
                 <td align=center>".$_SESSION['lang']['kodegolongan']."</td>
                 <td align=center>".$_SESSION['lang']['lokasitugas']."</td>
                 <td align=center>".$_SESSION['lang']['pt']."</td>
                 <td align=center>".$_SESSION['lang']['subunit']."</td>
                 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['statuspajak'])."</td>
                 <td align=center>".$_SESSION['lang']['tanggalmasuk']."</td>
                 <td align=center>".$_SESSION['lang']['tanggalkeluar']."</td>
                 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['tipekaryawan'])."</td>
                 <td align=center>".$_SESSION['lang']['action']."</td>
                 
           </tr>
         </thead>

         <tbody id=searchplaceresult>
         </tbody>
         <tfoot>
         </tfoot> 
                 <tr align=center><td colspan=13 align=center>
         <button align=center class=mybutton value=0 onclick=prefDatakaryawan1(this,this.value) id=prefbtn>< ".$_SESSION['lang']['pref']." </button> 
         &nbsp 
         <button align=center class=mybutton value=2 onclick=nextDatakaryawan1(this,this.value) id=nextbtn> ".$_SESSION['lang']['lanjut']." ></button>
        </td><tr>
         </table>
     </fieldset>
	 </div>";

CLOSE_BOX();
close_body('');
?>