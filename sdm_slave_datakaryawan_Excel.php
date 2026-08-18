<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmbag=makeOption($dbname,'sdm_5departemen','kode,nama');
$nmsuku=makeOption($dbname,'sdm_5suku','idsuku,namasuku');
$country   =readCountry("./config/country.lst");
$nmcountry=array();
for($x=0;$x<count($country);$x++){
    $nmcountry[$country[$x][2]]=$country[$x][0];
}
if(isset($_GET['txtsearch']))
{
    $txtsearch=$_GET['txtsearch'];
    $ptsearch=$_GET['ptsearch'];
    $orgsearch=$_GET['orgsearch'];	
    $tipesearch=$_GET['tipesearch'];
    $statussearch=$_GET['statussearch'];	
    $thnmsk=$_GET['thnmsk'];
    $blnmsk=$_GET['blnmsk'];
    $thnkel=$_GET['thnkel'];
    $blnkel=$_GET['blnkel'];
    $schjk=$_GET['schjk'];
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
            if($_GET['ptsearch'] != ''){
                    $where .=" and a.kodeorganisasi='".$_GET['ptsearch']."'";
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
    $tahunini = date("Y");
    if($statussearch=='*')
//	   $where .=" and (a.tanggalkeluar!='0000-00-00')";
       $where .=" and (a.tanggalkeluar='0000-00-00' or a.tanggalkeluar<'".$hariini."')"; // tidak aktif
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
$listalokasi=array(0=>'Unit',1=>'Umum');
$listOrg=ambilLokasiTugasDanTurunannya('list',$_SESSION['empl']['lokasitugas']);
$list=str_replace("|","','",$listOrg);
$list="'".$list."'";

if(trim($_SESSION['empl']['tipelokasitugas'])=='HOLDING')
{
$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e where 
	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan
	  ".$where." order by a.namakaryawan ASC";
	  
$strd="select b.*,a.namakaryawan,c.kelompok, case b.status when 1 then 'Y' when 0 then 'T' end as statusx, IF(b.emplasment = '1','Y','T') as emplasment
       from ".$dbname.".sdm_karyawankeluarga b
       left join ".$dbname.".datakaryawan a
	   on b.karyawanid=a.karyawanid
	   left join ".$dbname.".sdm_5pendidikan c on b.levelpendidikan=c.levelpendidikan
	   where 1=1 ".$where;
	  
 }
else if(trim($_SESSION['empl']['tipelokasitugas'])=='KANWIL')
{
$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e  
	  where 
	  a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan and a.tipekaryawan not in ('0','7','8')
	  ".$where."  order by a.namakaryawan ASC";
	  
	$strd="select b.*,a.namakaryawan,c.kelompok, case b.status when 1 then 'Y' when 0 then 'T' end as statusx, IF(b.emplasment = '1','Y','T') as emplasment 
       from ".$dbname.".sdm_karyawankeluarga b
       left join ".$dbname.".datakaryawan a
	   on b.karyawanid=a.karyawanid
	   left join ".$dbname.".sdm_5pendidikan c on b.levelpendidikan=c.levelpendidikan
	   where a.tipekaryawan!=0 and a.lokasitugas in(".$list.") ".$where." order by a.namakaryawan ASC"; 
}
else
{
//a.tipekaryawan!=0 orang yang tidak di pusat tidak dapat melihat data orang permanent
$str="select a.*,b.namajabatan,c.namagolongan,d.tipe,e.kelompok from ".$dbname.".datakaryawan a, 
      ".$dbname.".sdm_5jabatan b, ".$dbname.".sdm_5golongan c, ".$dbname.".sdm_5tipekaryawan d, ".$dbname.".sdm_5pendidikan e where 
      lokasitugas in(".$list.")
	  and a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	  and d.id=a.tipekaryawan and a.levelpendidikan=e.levelpendidikan and a.tipekaryawan not in ('0','7','8')
	  and a.tipekaryawan!=0 and lokasitugas in(".$list.") ".$where." order by a.namakaryawan ASC";
	  
$strd="select b.*,a.namakaryawan,c.kelompok, case b.status when 1 then 'Y' when 0 then 'T' end as statusx, IF(b.emplasment = '1','Y','T') as emplasment 
       from ".$dbname.".sdm_karyawankeluarga b
       left join ".$dbname.".datakaryawan a
	   on b.karyawanid=a.karyawanid
	   left join ".$dbname.".sdm_5pendidikan c on b.levelpendidikan=c.levelpendidikan
	   where a.tipekaryawan!=0 and a.lokasitugas in(".$list.") ".$where." order by a.namakaryawan ASC"; 
}
//echo $str;
//=====================
$stream='<style> .str{ mso-number-format:\@; } </style>';

   $stream.="
       Daftar karyawan:
	   <table border=1>
	   <tr>
	     <td align=center>No.</td>
 		
		 <td align=center>".$_SESSION['lang']['nik']."</td>
		 <td align=center>".$_SESSION['lang']['employeename']."</td>
		 <td align=center>".$_SESSION['lang']['kodejabatan']."</td>
		 <td align=center>".$_SESSION['lang']['levelname']."</td>
		 <td align=center>".$_SESSION['lang']['lokasitugas']."</td>
		 <td align=center>".$_SESSION['lang']['pt']."</td>
		 <td align=center>".$_SESSION['lang']['passport']."</td>
         <td align=center>".$_SESSION['lang']['noktp']."</td>
		 <td align=center>".$_SESSION['lang']['levelpendidikan']."</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['statuspajak'])."</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['statusperkawinan'])."</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['jumlahanak'])."</td>
         <td align=center>".$_SESSION['lang']['tanggalpengangkatan']."</td>
		 <td align=center>".$_SESSION['lang']['tanggalmasuk']."</td>
		 <td align=center>".$_SESSION['lang']['masakerja']." (".$_SESSION['lang']['tahun'].")</td>
		 <td align=center>".str_replace(" ","<br>",$_SESSION['lang']['tipekaryawan'])."</td>
         <td align=center>".$_SESSION['lang']['suku']."</td>
         <td align=center>KPP Perusahaan</td>
		 <td align=center>".$_SESSION['lang']['tempatlahir']."</td>
		 <td align=center>".$_SESSION['lang']['tanggallahir']."</td>
		 <td align=center>".$_SESSION['lang']['umur']." (".$_SESSION['lang']['tahun'].")</td>
		 <td align=center>".$_SESSION['lang']['warganegara']."</td>
		 <td align=center>".$_SESSION['lang']['jeniskelamin']."</td>
		 <td align=center>".$_SESSION['lang']['tanggalmenikah']."</td>
		 <td align=center>".$_SESSION['lang']['agama']."</td>
		 <td align=center>".$_SESSION['lang']['golongandarah']."</td>
		 <td align=center>".$_SESSION['lang']['alamataktif']."</td>
		 <td align=center>".$_SESSION['lang']['provinsi']."</td>
		 <td align=center>".$_SESSION['lang']['kodepos']."</td>
         <td align=center>".$_SESSION['lang']['noteleponrumah']."</td>
		 <td align=center>".$_SESSION['lang']['nohp']."</td>
         <td align=center>".$_SESSION['lang']['nohp']."2</td>
		 <td align=center>A/N Rekening</td>
         <td align=center>".$_SESSION['lang']['norekeningbank']."</td>
		 <td align=center>".$_SESSION['lang']['namabank']."</td>
		 <td align=center>".$_SESSION['lang']['sistemgaji']."</td>
		 <td align=center>".$_SESSION['lang']['nomor']." SIM</td>
		 <td align=center>".$_SESSION['lang']['notelepondarurat']."</td>
   		 <td align=center>".$_SESSION['lang']['tanggalkeluar']."</td>
		 <td align=center>".$_SESSION['lang']['jumlahtanggunganbpjs']."</td>
		 <td align=center>".$_SESSION['lang']['npwp']."</td>
		 <td align=center>".$_SESSION['lang']['lokasipenerimaan']."</td>
		 <td align=center>".$_SESSION['lang']['department']."</td>
		 <td align=center>Sub ".$_SESSION['lang']['department']."</td>
         <td align=center>".$_SESSION['lang']['divisi']."</td>
         <td align=center>".$_SESSION['lang']['bpjs']." ".$_SESSION['lang']['kesehatan']."</td>    
         <td align=center>".$_SESSION['lang']['jms']."</td>    
         <td align=center>".$_SESSION['lang']['bpjs']." Pensiun</td>   
		 <td align=center>".$_SESSION['lang']['email']." Pribadi</td>
         <td align=center>".$_SESSION['lang']['email']." Kantor</td>
         <td align=center>".$_SESSION['lang']['status']." ".$_SESSION['lang']['karyawan']."</td>
         <td align=center>".$_SESSION['lang']['alokasibiaya']."</td>
	     </tr>";
   
function getAge($tdate,$dob)
{
        $age = 0;
        while( $tdate > $dob = strtotime('+1 year', $dob))
        {
                ++$age;
        }
        return $age;
}   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows<1)
{
	$stream.="<tr><td>NOT FOUND</td></tr>";
}
else
{
    $no=0;
    while($bar=$res->fetch())
    {
            $no+=1;
                $masakerja=getAge(strtotime($hariini),strtotime($bar->tanggalmasuk));
                $usia=getAge(strtotime($tahunini),strtotime($bar->tanggallahir))+1;
            $stream.="<tr>
                 <td>".$no."</td>
                     
                     <td style='mso-number-format:\@;'>".$bar->nik."</td>
                     <td>".$bar->namakaryawan."</td>
                     <td>".$bar->namajabatan."</td>
                     <td>".$bar->namagolongan."</td>
                     <td>".$bar->lokasitugas."</td>
                     <td>".$bar->kodeorganisasi."</td>
                     <td>'".$bar->no_keluarga."</td>
                     <td style='mso-number-format:\@;'>'".$bar->noktp."</td>
                     <td>".$bar->kelompok."</td>
                     <td>".$bar->statuspajak."</td>
                     <td>".$bar->statusperkawinan."</td>
                     <td align=right >".$bar->jumlahanak."</td>
                     <td>".$bar->tanggalpengangkatan."</td>
                     <td>".$bar->tanggalmasuk."</td>
                     <td>".$masakerja."</td>
                     <td>".$bar->tipe."</td>
                     <td>".$nmsuku[$bar->suku]."</td>
                     <td>".$bar->kppnpwp."</td>
                     <td>".$bar->tempatlahir."</td>
                     <td>".$bar->tanggallahir."</td>
                     <td>".$usia."</td>
                     <td>".$nmcountry[$bar->warganegara]."</td>
                     <td>".$bar->jeniskelamin."</td>
                     <td>".$bar->tanggalmenikah."</td>
                     <td>".$bar->agama."</td>
                     <td>".$bar->golongandarah."</td>
                     <td>".$bar->alamataktif."</td>
                     <td>".$bar->provinsi."</td>
                     <td>".$bar->kodepos."</td>
                     <td style='mso-number-format:\@;'>".$bar->noteleponrumah."</td>
                     <td style='mso-number-format:\@;'>".$bar->nohp."</td>
                     <td style='mso-number-format:\@;'>".$bar->nohp2."</td>
                     <td>".$bar->pemilikrekening."</td>
                     <td style='mso-number-format:\@;'>".$bar->norekeningbank."</td>
                     <td>".$bar->namabank."</td>
                     <td>".$bar->sistemgaji."</td>
                     <td style='mso-number-format:\@;'>".$bar->sim."</td>
                     <td>".$bar->notelepondarurat."</td>
                     <td>".$bar->tanggalkeluar."</td>
                     <td>".$bar->jumlahtanggungan."</td>
                     <td>".$bar->npwp."</td>
                     <td>".$bar->lokasipenerimaan."</td>
                     <td>".$nmbag[$bar->bagian]."</td>
                     <td>".$nmbag[$bar->subdept]."</td>
                     <td>".$nmorg[$bar->subbagian]."</td>
                      <td>".$bar->bpjs."</td>    
                      <td>".$bar->jms."</td>    
                      <td>".$bar->pensiun."</td>    
                      <td>".$bar->email."</td>    
                      <td>".$bar->emailkantor."</td>    
                       <td>".$bar->statuskaryawan."</td>	
                       <td>".$listalokasi[$bar->alokasi]."</td>     
	  </tr>";			 		  
	}
	$stream.="</table>";
	
//============================keluarga
$stream.= "KELUARGA";
   $stream.="<table border=1>
	   <tr>
	     <td align=center>No.</td>
 		
		 <td align=center>".$_SESSION['lang']['nama']."</td>
		 <td align=center>".$_SESSION['lang']['anggotakeluarga']."</td>
		 <td align=center>".$_SESSION['lang']['jeniskelamin']."</td>
		 <td align=center>".$_SESSION['lang']['hubungan']."</td>
	 	 <td align=center>".$_SESSION['lang']['tempatlahir']."</td>
		 <td align=center>".$_SESSION['lang']['tanggallahir']."</td>		 		 
		 <td align=center>".$_SESSION['lang']['pekerjaan']."</td> 
		 <td align=center>".$_SESSION['lang']['statusperkawinan']."</td>	 
		 <td align=center>".$_SESSION['lang']['pendidikan']."</td>		 
		 <td align=center>".$_SESSION['lang']['email']."</td>
		 <td align=center>".$_SESSION['lang']['telp']."</td>	 
		 <td align=center>".$_SESSION['lang']['tanggungan']."</td>
		 <td align=center>".$_SESSION['lang']['emplasment']."</td>
	     </tr>";
$res=$owlPDO->query($strd) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);   
$no=0;
while($bar=$res->fetch())
{
    $no+=1;
   $stream.="<table border=1>
                    <tr>
                    <td>".$no."</td>
                
                    <td>".$bar->namakaryawan."</td>
                    <td>".$bar->nama."</td>
                    <td>".$bar->jeniskelamin."</td>
                    <td>".$bar->hubungankeluarga."</td>
                    <td>".$bar->tempatlahir."</td>
                    <td>".tanggalnormal($bar->tanggallahir)."</td>		 		 
                    <td>".$bar->pekerjaan."</td> 
                    <td>".$bar->status."</td>	 
                    <td>".$bar->kelompok."</td>		 
                    <td>".$bar->email."</td>
                    <td>".$bar->telp."</td>	 
                    <td>".$bar->statusx."</td>
                    <td>".$bar->emplasment."</td>
                    </tr>";		
}
$stream.="</table>";
}
$wktu=date("Hms");
$titlelaporan="DT_Employee_".$wktu."__".date('Y');
if($handle = opendir('tempExcel')){
	while(false !== ($file = readdir($handle))){
		if($file != "." && $file != ".." && $file != "index.html"){
			@unlink('tempExcel/' . $file);
		}
	}
	closedir($handle);
}
$handle = fopen("tempExcel/".$titlelaporan.".xls",'w');
if(!fwrite($handle, $stream)){
	echo "<script language=javascript1.2>
		parent.window.alert('Cant convert to excel format');
	</script>";
	exit;
}else{
	echo "<script language=javascript1.2>
		window.location='tempExcel/".$titlelaporan.".xls';
		</script>";
}
closedir($handle);
?>