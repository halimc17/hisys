<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');

$getrows=20;
//default query
if($_POST['page'])
   $page=$_POST['page'];
else
   $page=1; 
  
$maxdisplay=($page*$getrows-20);

if(isset($_POST['txtsearch']))
{
    $txtsearch=$_POST['txtsearch'];
    $ptsearch=$_POST['ptsearch'];	
    $orgsearch=$_POST['orgsearch'];	
    $tipesearch=$_POST['tipesearch'];
    $statussearch=$_POST['statussearch'];	
    $thnmsk=$_POST['thnmsk'];
    $blnmsk=$_POST['blnmsk'];
    $thnkel=$_POST['thnkel'];
    $blnkel=$_POST['blnkel'];
    $schjk=$_POST['schjk'];
	

}
else
{
    $txtsearch='';
    $ptsearch='';	
    $orgsearch='';	
    $tipesearch='';
    $statussearch='';	
    $thnmsk='';
    $blnmsk='';
    $thnkel='';
    $blnkel='';
    $schjk='';
}

if(isset($_POST['method'])){
    $optpt="";
    $optpt="<option value=''>".$_SESSION['lang']['all']."</option>";

    if($_POST['ptsearch'] != ''){
            $str="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                    where induk='".$_POST['ptsearch']."' order by namaorganisasi asc";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
            }
    }else{
            $str="select distinct a.lokasitugas as kodeorganisasi,namaorganisasi from ".$dbname.".datakaryawan a 
          left join ".$dbname.".organisasi b on a.lokasitugas=b.kodeorganisasi order by namaorganisasi asc";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
            {
                    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";	
            }
    }
    echo $optpt;
}


$where='';
if($txtsearch!='')
   $where= " and a.namakaryawan like '%".$txtsearch."%'";
if($orgsearch!='')
{
   $where .=" and (a.lokasitugas='".$orgsearch."' or a.subbagian='".$orgsearch."') ";    
}
else
{
    if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')
    {
        if($_POST['ptsearch'] != ''){
                $where .=" and a.kodeorganisasi='".$_POST['ptsearch']."'";
        }else{
                $where .="";
        }
    }
    else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL')
    {
        $where .=" and a.lokasitugas in (select kodeorganisasi from ".$dbname.".organisasi where "
                . " induk='".$_SESSION['empl']['kodeorganisasi']."') ";    
    }
    else
    {
        $where .=" and a.lokasitugas='".$_SESSION['empl']['lokasitugas']."' ";    
    }
}


if($tipesearch!='')
{
if($tipesearch==100){
    $where.=" and a.tipekaryawan!=4 ";
}
else{
   $where .=" and a.tipekaryawan='".$tipesearch."'"; 
}
}
    if($thnmsk!='')
    {
            $where.="and left(a.tanggalmasuk,4)='".$thnmsk."'   ";
    }


    if($blnmsk!='')
    {
            $where.="and mid(a.tanggalmasuk,6,2)='".$blnmsk."'  ";
    }

    if($thnkel!='')
    {
            $where.="and left(a.tanggalkeluar,4)='".$thnkel."'  ";
    }


    if($blnkel!='')
    {
            $where.="and mid(a.tanggalkeluar,6,2)='".$blnkel."' ";
    }   
   
    $hariini = date("Y-m-d");
    if($statussearch=='*')
//	   $where .=" and (a.tanggalkeluar!='0000-00-00')";
       $where .=" and (a.tanggalkeluar!='0000-00-00' and a.tanggalkeluar<'".$hariini."')"; // tidak aktif
    else if($statussearch=='0000-00-00')
//	   $where .=" and (a.tanggalkeluar='0000-00-00')";
       $where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar>='".$hariini."')"; // masih aktif
    else
    {} 

     if($schjk!='')
     {
             $where.=" and a.jeniskelamin='".$schjk."'";
     }

//make sure user can only access allowed data   
$listOrg=ambilLokasiTugasDanTurunannya('list',$_SESSION['empl']['lokasitugas']);
$list=str_replace("|","','",$listOrg);
$list="'".$list."'";

if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')
{
$str="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan 
	  ".$where."
	  limit ".$maxdisplay.",".$getrows ;
	  
//	 echo $str;    
 $strx="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan 
	  ".$where."";  
}

else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL')
{
  $str="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan and a.tipekaryawan not in ('0','7','8')
	  ".$where."
	  limit ".$maxdisplay.",".$getrows
	  ;
	  
//	 echo $str;    
 $strx="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan and a.tipekaryawan not in ('0','7','8')
	  ".$where."";  
  
}

else
{
//a.tipekaryawan!=0 orang yang tidak di pusat tidak dapat melihat data orang permanent
$str="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
      lokasitugas in(".$list.")
	  and a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan and a.tipekaryawan not in ('0','7','8')
	  ".$where." order by a.namakaryawan ASC 
	  limit ".$maxdisplay.",".$getrows
	  ;
 $strx="select a.*,b.namajabatan,c.namagolongan,d.tipe from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c,  ".$dbname.".sdm_5tipekaryawan d where 
      lokasitugas in(".$list.")
	  and a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan and a.tipekaryawan not in ('0','7','8')
	  ".$where.""; 	     	
}

//==================jlh karyawan
$jlhkar=0;
$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($resx);
$jlhkar=$numrows;

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);

    $no=$maxdisplay;
    if($jlhkar==0)
    {
            echo"<tr><td colspan=2>DATA NOT FOUND</td></tr>";	
    }
    if($jlhkar!==0)
    {
            echo"<tr><td colspan=2>Total: ".$jlhkar." Person</td></tr>";	
    }
    while($bar=$res->fetch())
    {
            //get pendidikan terakhir
            $str1="select a.kelompok from ".$dbname.".sdm_5pendidikan a
                   where a.levelpendidikan=".$bar->levelpendidikan." "; 
            $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res1->setFetchMode(PDO::FETCH_OBJ);
            $pendidikan="";
            while($barpendidikan=$res1->fetch())
            {
                    $pendidikan=(isset($barpendidikan->kelompok) ? $barpendidikan->kelompok : "");
            }

            $no+=1;
            echo "<tr class=rowcontent>
                 <td>".$no."</td>
                     <td width=85>".$bar->nik."</td>
                     <td>".$bar->namakaryawan."</td>
                     <td>".$bar->namajabatan."</td>
                     <td>".$bar->namagolongan."</td>
                     <td>".$bar->lokasitugas."</td>
                     <td>".$bar->kodeorganisasi."</td>
                     <td>".$bar->subbagian."</td>
                     <td>".$bar->statuspajak."</td>
                     <td>".tanggalnormal($bar->tanggalmasuk)."</td>
                     <td>".tanggalnormal($bar->tanggalkeluar)."</td>
                     <td>".$bar->tipe."</td>
                     <td>
                                <img src=images/zoom.png class=resicon  title='".$_SESSION['lang']['view']."' onclick=\"previewKaryawan('".$bar->karyawanid."','".$bar->namakaryawan."',event);\">
                                    <img src=images/pdf.jpg class=resicon  title='".$_SESSION['lang']['pdf']."' onclick=\"previewKaryawanPDF('".$bar->karyawanid."','".$bar->namakaryawan."',event);\">		 
                     </td>
                      </tr>";			 		  
    }
?>