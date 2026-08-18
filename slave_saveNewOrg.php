<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

        $parent		=strtoupper(trim($_POST['parent']));
        $orgcode	=strtoupper(trim($_POST['orgcode']));
        $orginitial	=strtoupper(trim($_POST['orginitial']));
        $orgname    =strtoupper(trim($_POST['orgname']));
        $orgtype    =strtoupper(trim($_POST['orgtype']));
        $blokinduk    =strtoupper(trim($_POST['blokinduk']));
        $namablokinduk    =strtoupper(trim($_POST['namablokinduk']));
        $orgidentnik=strtoupper(trim($_POST['orgidentnik']));
        $orgadd		=trim($_POST['orgadd']);
        $orgcity	=strtoupper(trim($_POST['orgcity']));
        $orgcountry	=strtoupper(trim($_POST['orgcountry']));											
        $orgzip 	=strtoupper(trim($_POST['orgzip']));
        $orgtelp	=strtoupper(trim($_POST['orgtelp']));
        $orgdetail  =$_POST['orgdetail'];
        $alokasi	=strtoupper(trim($_POST['alokasi']));
        $noakun		=strtoupper(trim($_POST['noakun']));
		
        $tipepabrik		=strtoupper(trim($_POST['tipepabrik']));		
        $sustainable		=strtoupper(trim($_POST['sustainable']));		
        $sertifikat		=strtoupper(trim($_POST['sertifikat']));		

        //check if the same code and the same parent already exist
        $jum=0;//indicate not exist
        $exist=false;
        $s1=$owlPDO->query("select count(*) from ".$dbname.".organisasi where kodeorganisasi='".$orgcode."' and induk='".$parent."'");
                    $s1->setFetchMode(PDO::FETCH_NUM);
        while($row=$s1->fetch())
        {
                $jum=$row[0];
        }
        if($jum>0)
          $exist=true;

        if(!$exist){//then insert
                $st2="insert into ".$dbname.".organisasi
                      (kodeorganisasi,namaorganisasi,alamat,telepon,wilayahkota,kodepos,induk,negara,tipe,lastuser,
					  detail,alokasi,noakun,identnik,inisialisasiorganisasi,
					  tipepabrik,sustainable,sertifikat,indukblok,namaindukblok)
                values('".$orgcode."','".$orgname."','".$orgadd."','".$orgtelp."','".$orgcity."','".$orgzip."','".$parent."','".$orgcountry."','".$orgtype."','".$_SESSION['standard']['username']."','".$orgdetail."','".$alokasi."','".$noakun."','".$orgidentnik."','".$orginitial."','".$tipepabrik."','".$sustainable."','".$sertifikat."','".$blokinduk."','".$namablokinduk."')";
        }
        else
        {//then update
          $st2="update ".$dbname.".organisasi
                set	namaorganisasi='".$orgname."',
                                alamat	='".$orgadd."',
                                telepon	='".$orgtelp."',
                                wilayahkota	='".$orgcity."',
                                kodepos	='".$orgzip."',
                                negara	='".$orgcountry."',
                                tipe	='".$orgtype."',
                                detail  =".$orgdetail.",
                                alokasi ='".$alokasi."',
                                noakun  ='".$noakun."',
                                identnik  ='".$orgidentnik."',
                                inisialisasiorganisasi  ='".$orginitial."',
								tipepabrik='".$tipepabrik."',
								sustainable='".$sustainable."',
								sertifikat='".$sertifikat."',
                                lastuser='".$_SESSION['standard']['username']."',
                                indukblok='".$blokinduk."',
                                namaindukblok='".$namablokinduk."'
                         where kodeorganisasi	='".$orgcode."'
                         and induk ='".$parent."'";	
        }
		
try{
          $owlPDO->exec($st2);         
  }
  catch (PDOException $e) {
             print " Gagal  !: " . $e->getMessage() . "<br/>";
             die();
      }
?>
