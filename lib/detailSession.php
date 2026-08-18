<?
require_once('nangkoelib.php');
require_once('zLib.php');
function setEmplSession($owlPDO,$userid,$dbname)//load all data from user_empl to session
{
	$strses1=$owlPDO->query("select * from ".$dbname.".datakaryawan where karyawanid=".$userid);
	$strses1->setFetchMode(PDO::FETCH_OBJ);
    $count = owlBaris($strses1);
	if($count>0){
		while($barses1=$strses1->fetch()){
          #ambil kodeorg
          $sKd="select kodeorg,induk from ".$dbname.".user a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
                where karyawanid='".$userid."' ";
          $rKd=fetchdata($sKd);
          $barses1->kodeorganisasi=$rKd[0]['induk'];
          $barses1->lokasitugas=$rKd[0]['kodeorg'];
          
		  $_SESSION['empl']['name']=$barses1->namakaryawan;
		  $_SESSION['empl']['sex']=$barses1->jeniskelamin;
		  $_SESSION['empl']['birthday']=$barses1->tanggallahir;
		  $_SESSION['empl']['birthplace']=$barses1->tempatlahir;
		  $_SESSION['empl']['address']=$barses1->alamataktif;
		  $_SESSION['empl']['noktp']=$barses1->noktp; 			//'identity num/no ktp',
                                          $_SESSION['empl']['nopaspor']=$barses1->nopaspor;
		  $_SESSION['empl']['nationality']=$barses1->warganegara;
		  $_SESSION['empl']['religion']=$barses1->agama;
		  $_SESSION['empl']['statusperkawinan']=$barses1->statusperkawinan;		//'status pajak/k1=kawin 1 anak',
		  $_SESSION['empl']['jabatan']=$barses1->kodejabatan;			//'id jabatan',
		  $_SESSION['empl']['kodeorganisasi']=$barses1->kodeorganisasi;		//'unit pemberi kerja',
		  $_SESSION['empl']['lokasitugas']=$barses1->lokasitugas;		//'lokasi kerja',
		  $_SESSION['empl']['poh']=$barses1->lokasipenerimaan;				//'point of hire',
		  $_SESSION['empl']['signdate']=$barses1->tanggalmasuk;		//'tgl masuk',
		  $_SESSION['empl']['resigndate']=$barses1->tanggalkeluar;	//'tgl keluar',
		  $_SESSION['empl']['sistemgaji']=$barses1->sistemgaji;			//'employment is payroll active or not/employee scorrs',
		  $_SESSION['empl']['email']=$barses1->email;
		  $_SESSION['empl']['phone']=$barses1->noteleponrumah;
		  $_SESSION['empl']['tipekaryawan']=$barses1->tipekaryawan;		//'bentuk ikatan',
		  $_SESSION['empl']['bagian']=$barses1->bagian;//'Id departement',
		  $_SESSION['empl']['kodejabatan']=$barses1->kodejabatan;
		  $_SESSION['empl']['kodegolongan']=$barses1->kodegolongan;
                                          $_SESSION['empl']['subbagian']=$barses1->subbagian;
		  //ambil tipe induk organisasi
		  $strx= $owlPDO->query("select tipe from  ".$dbname.".organisasi where kodeorganisasi='".$barses1->lokasitugas."'");
		  $strx->setFetchMode(PDO::FETCH_OBJ);
		  $_SESSION['empl']['tipelokasitugas']='';
		  while($barx=$strx->fetch())
		  {
		  	$_SESSION['empl']['tipelokasitugas']=$barx->tipe;
		  }
		}
                                //ambil wilayah
		  $strx= $owlPDO->query("select regional from  ".$dbname.".bgt_regional_assignment where 
                                             kodeunit='".$_SESSION['empl']['lokasitugas']."'");
		  $strx->setFetchMode(PDO::FETCH_OBJ);
		  $_SESSION['empl']['regional']='';
		  while($barx=$strx->fetch())
		  {
		  	$_SESSION['empl']['regional']=$barx->regional;
		  }   
	}
}

function getPrivillageType($owlPDO,$dbname)//privillage type
{
	$strses2=$owlPDO->query("select access_name from ".$dbname.".tipeakses
                    where status=1");
	$strses2->setFetchMode(PDO::FETCH_OBJ);
    $count = owlBaris($strses2);
	if($count>0)
	{
		while($barses2=$strses2->fetch()){
		    $_SESSION['access_type']=$barses2->access_name;
		}
		 
		if(isset($_SESSION['access_type']))
		 	return true;
		else
		    return false;
	}
	else
	{
		return false;
	}
}

function getPrivillages($owlPDO,$username,$dbname)//get user privillages
{
	$strdet=$owlPDO->query("select a.* from ".$dbname.".user_orgdetail a left join ".$dbname.".organisasi b on a.kodeorganisasi=b.kodeorganisasi where namauser='".$username."' order by induk asc, tipe desc,inti desc, a.kodeorganisasi asc");	  
	$strdet->setFetchMode(PDO::FETCH_OBJ);
    $countdet = owlBaris($strdet);
	if($countdet>0)
	{
		$x=0;
	  	while($bardet=$strdet->fetch())
		{
			$_SESSION['orgdet'][$x] = $bardet->kodeorganisasi;
			$x+=1;
		}
	}
	
	$strses3=$owlPDO->query("select * from ".$dbname.".auth
	          where namauser='".$username."'
			  and status=1");	  
	$strses3->setFetchMode(PDO::FETCH_OBJ);
    $count = owlBaris($strses3);
	if($count>0)
	{
	  	$x=0;
		while($barses3=$strses3->fetch())
		{
        if($x==0)
           $c_o=$barses3->menuid;
        else
           $c_o.=",".$barses3->menuid;
		  $_SESSION['priv'][$x]=$barses3->menuid;
		  $_SESSION['priv'][$barses3->menuid.'detail']=$barses3->detail;
		  $x+=1; 	   
		}
		$_SESSION['allpriv']=$c_o;
                                        if(count($_SESSION['priv'])>0)
                                                   return true;
                                       else
                                                   return false;	
	}
	else
                    return false; 
}

function getPrivillagesbi($owlPDO,$username,$dbname){
	## get user privillages
	$strdet=$owlPDO->query("select * from ".$dbname.".user_orgdetail where namauser='".$username."'");	  
	$strdet->setFetchMode(PDO::FETCH_OBJ);
    $countdet = owlBaris($strdet);
	if($countdet>0)
	{
		$x=0;
	  	while($bardet=$strdet->fetch())
		{
			$_SESSION['orgdet'][$x] = $bardet->kodeorganisasi;
			$x+=1;
		}
	}
	
	$strses3=$owlPDO->query("select * from ".$dbname.".authbi
	          where namauser='".$username."'
			  and status=1");	  
	$strses3->setFetchMode(PDO::FETCH_OBJ);
    $count = owlBaris($strses3);
	if($count>0)
	{
	  	$x=0;
		while($barses3=$strses3->fetch())
		{
        if($x==0)
           $c_o=$barses3->menuid;
        else
           $c_o.=",".$barses3->menuid;
		  $_SESSION['priv'][$x]=$barses3->menuid;
		  $_SESSION['priv'][$barses3->menuid.'detail']=$barses3->detail;
		  $x+=1; 	   
		}
		$_SESSION['allpriv']=$c_o;
                                        if(count($_SESSION['priv'])>0)
                                                   return true;
                                       else
                                                   return false;	
	}
	else
                    return false; 
}

function setEmployer($owlPDO,$dbname)
{
//$_SESSION['theme'] = 'skyblue';
$strses4=$owlPDO->query("select * from ".$dbname.".organisasi
          where kodeorganisasi='".$_SESSION['empl']['kodeorganisasi']."'");			    
$strses4->setFetchMode(PDO::FETCH_OBJ);
$count = owlBaris($strses4);
if($count>0)
{
        while($barses4=$strses4->fetch())
        {
                $_SESSION['org']['kodeorganisasi']=$barses4->kodeorganisasi;
                $_SESSION['org']['namaorganisasi']=$barses4->namaorganisasi;
                $_SESSION['org']['tipeorganisasi']=$barses4->tipe;
                $_SESSION['org']['alamat']=$barses4->alamat;
                $_SESSION['org']['telepon']=$barses4->telepon;
                $_SESSION['org']['wilayahkota']=$barses4->wilayahkota;
                $_SESSION['org']['induk']=$barses4->induk;
                $_SESSION['org']['tipeinduk']='';
        } 
//set tipelokasitugas
        $strses4a=$owlPDO->query("select * from ".$dbname.".organisasi
                  where kodeorganisasi='".$_SESSION['org']['induk']."'");			    
        $strses4a->setFetchMode(PDO::FETCH_OBJ);
        $count = owlBaris($strses4a);	
        if($count>0)
        {
                while($barses4a=$strses4a->fetch())
                {
                  $_SESSION['org']['tipeinduk']=$barses4a->tipe;		
                }
        }
}	
else{
$_SESSION['org']=NULL;
            }
        $strses5=$owlPDO->query("select * from ".$dbname.".setup_periodeakuntansi
                  where kodeorg='".$_SESSION['empl']['lokasitugas']."' and tutupbuku=0");
        $strses5->setFetchMode(PDO::FETCH_OBJ);
        $count = owlBaris($strses5);
        if($count>0)
        {
                while($barses5=$strses5->fetch())
                {
                        $tmpPeriod = str_replace("-", "",$barses5->periode);
                        $tmpPeriod = str_replace("/", "",$tmpPeriod);
                        $_SESSION['org']['period']['start']=str_replace("-","",$barses5->tanggalmulai);
                        $_SESSION['org']['period']['end']=str_replace("-","",$barses5->tanggalsampai);
                        $_SESSION['org']['period']['bulan']=substr($tmpPeriod,4,2);
                        $_SESSION['org']['period']['tahun']=substr($tmpPeriod,0,4);
                }
        }
        else
        $_SESSION['org']['period']='';

//tampilkan semua periode akuntansi untuk gudang=========================
        $strses6=$owlPDO->query("select a.* from ".$dbname.".setup_periodeakuntansi a
                  left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
                          where b.tipe like 'GUDANG%'");	  
        $strses6->setFetchMode(PDO::FETCH_OBJ);
        $count = owlBaris($strses6);
        if($count>0)
        {
                while($barses6=$strses6->fetch())
                {
                        $tmpPeriod = str_replace("-", "",$barses6->periode);
                        $tmpPeriod = str_replace("/", "",$tmpPeriod);
                        $_SESSION['gudang'][$barses6->kodeorg]['start']=str_replace("-","",$barses6->tanggalmulai);
                        $_SESSION['gudang'][$barses6->kodeorg]['end']=str_replace("-","",$barses6->tanggalsampai);
                        $_SESSION['gudang'][$barses6->kodeorg]['bulan']=substr($tmpPeriod,4,2);
                        $_SESSION['gudang'][$barses6->kodeorg]['tahun']=substr($tmpPeriod,0,4);
                }
        }
        else{
               $_SESSION['period']='';	
        }
     //ambil nama holding ybs
        $str=$owlPDO->query("select namaorganisasi from ".$dbname.".organisasi where tipe='HOLDING' and (induk='' or induk is null) limit 1");
        $str->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$str->fetch())
        {
            $_SESSION['org']['holding']=trim($bar->namaorganisasi);
        }
}
?>