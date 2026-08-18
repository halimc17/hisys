<?php
//ambil kode organisasi dari lokasitugas sampai afdeling
//return type: Array,List,Option
function ambilLokasiTugasDanTurunannya($returntype='array',$lokasitugas)
{

        global $dbname;
        global $conn;
        global $owlPDO;
        $arr=Array();
        $list='';
        $option="";
        $str="select distinct kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where kodeorganisasi='".$lokasitugas."' and tipe not in('BLOK','STENGINE','STATION') order by kodeorganisasi";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
                if($bar->tipe=='PT')//skip PT
                {continue;}
                else
                {
                        if($returntype=='array')
                          array_push($arr,$bar->kodeorganisasi);
                        else if($list=='' and $returntype=='list')
                          {
                                $list=$bar->kodeorganisasi;
                          }
                        else if($list!='' and $returntype=='list') 
                         {
                                $list.="|".$bar->kodeorganisasi;
                         } 
                         else
                         {
                                $option.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
                         }
                }
//second grade==============================
                        $str1="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$bar->kodeorganisasi."' and tipe not in('BLOK','STENGINE','STATION') order by kodeorganisasi";
                        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
                        $res1->setFetchMode(PDO::FETCH_OBJ);
                        while($bar1=$res1->fetch())
                        {
                                if($bar1->tipe=='PT')
                                {continue;}
                                else
                                {
                                        if($returntype=='array')
                                          array_push($arr,$bar1->kodeorganisasi);
                                        else if($list=='' and $returntype=='list')
                                          {
                                                $list=$bar1->kodeorganisasi;
                                          }
                                        else if($list!='' and $returntype=='list') 
                                         {
                                                $list.="|".$bar1->kodeorganisasi;
                                         } 
                                         else
                                         {
                                                $option.="<option value='".$bar1->kodeorganisasi."'>".$bar1->namaorganisasi."</option>";
                                         }
                                }
//third grade==============================
                                $str2="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$bar1->kodeorganisasi."' and tipe not in('BLOK','STENGINE','STATION') order by kodeorganisasi";
                                $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
                                $res2->setFetchMode(PDO::FETCH_OBJ);
                                while($bar2=$res2->fetch())
                                {
                                if($bar2->tipe=='PT')
                                {continue;}
                                else
                                {
                                        if($returntype=='array')
                                          array_push($arr,$bar2->kodeorganisasi);
                                        else if($list=='' and $returntype=='list')
                                          {
                                                $list=$bar2->kodeorganisasi;
                                          }
                                        else if($list!='' and $returntype=='list') 
                                         {
                                                $list.="|".$bar2->kodeorganisasi;
                                         } 
                                         else
                                         {
                                                $option.="<option value='".$bar2->kodeorganisasi."'>".$bar2->namaorganisasi."</option>";
                                         }
                                }
//forth grade==============================
                                                $str3="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$bar2->kodeorganisasi."' and tipe not in('BLOK','STENGINE','STATION') order by kodeorganisasi";
                                                $res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
                                                $res3->setFetchMode(PDO::FETCH_OBJ);
                                                while($bar3=$res3->fetch())
                                                {
                                                if($bar3->tipe=='PT')
                                                {continue;}
                                                else
                                                {
                                                        if($returntype=='array')
                                                          array_push($arr,$bar3->kodeorganisasi);
                                                        else if($list=='' and $returntype=='list')
                                                          {
                                                                $list=$bar3->kodeorganisasi;
                                                          }
                                                        else if($list!='' and $returntype=='list') 
                                                         {
                                                                $list.="|".$bar3->kodeorganisasi;
                                                         } 
                                                         else
                                                         {
                                                                $option.="<option value='".$bar3->kodeorganisasi."'>".$bar3->namaorganisasi."</option>";
                                                         }

                                                }
//fifth grade==============================
                                                                        $str4="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$bar3->kodeorganisasi."' and tipe not in('BLOK','STENGINE','STATION') order by kodeorganisasi";
                                                                        $res4=$owlPDO->query($str4) or die(print " Gagal: ".PDOException::getMessage());
                                                                        $res4->setFetchMode(PDO::FETCH_OBJ);
                                                                        while($bar4=$res4->fetch())
                                                                        {
                                                                        if($bar4->tipe=='PT')
                                                                        {continue;}
                                                                        else
                                                                        {
                                                                                if($returntype=='array')
                                                                                  array_push($arr,$bar4->kodeorganisasi);
                                                                                else if($list=='' and $returntype=='list')
                                                                                  {
                                                                                        $list=$bar4->kodeorganisasi;
                                                                                  }
                                                                                else if($list!='' and $returntype=='list') 
                                                                                 {
                                                                                        $list.="|".$bar4->kodeorganisasi;
                                                                                 } 
                                                                                 else
                                                                                 {
                                                                                        $option.="<option value='".$bar4->kodeorganisasi."'>".$bar4->namaorganisasi."</option>";
                                                                                 }
                                                                        }
//sixth grade==============================
                                                                                $str5="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$bar4->kodeorganisasi."' and tipe not in('BLOK','STENGINE','STATION') order by kodeorganisasi";
                                                                                $res5=$owlPDO->query($str5) or die(print " Gagal: ".PDOException::getMessage());
                                                                                $res5->setFetchMode(PDO::FETCH_OBJ);
                                                                                while($bar5=$res5->fetch())
                                                                                {
                                                                                if($bar5->tipe=='PT')
                                                                                {continue;}
                                                                                else
                                                                                {
                                                                                        if($returntype=='array')
                                                                                          array_push($arr,$bar5->kodeorganisasi);
                                                                                        else if($list=='' and $returntype=='list')
                                                                                          {
                                                                                                $list=$bar5->kodeorganisasi;
                                                                                          }
                                                                                        else if($list!='' and $returntype=='list') 
                                                                                         {
                                                                                                $list.="|".$bar5->kodeorganisasi;
                                                                                         } 
                                                                                         else
                                                                                         {
                                                                                                $option.="<option value='".$bar5->kodeorganisasi."'>".$bar5->namaorganisasi."</option>";
                                                                                         }
                                                                                }

                                                                                }										  
                                                             }		
                                      }

                          }	
                }
        }


if($returntype=='array')
        return $arr;
else if($returntype=='list')
        return $list;
else
    return $option;
}

function namakaryawan($db,$conn,$userid)
{
    global $owlPDO;
    
        $namakaryawan='';
                $strx="select namakaryawan from ".$db.".datakaryawan where karyawanid=".$userid;
                $res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($barx=$res->fetch())
                {
                        $namakaryawan=$barx->namakaryawan;
                }
        return $namakaryawan;		
}

function ambilUnitPembebananBarang($returntype='array'){

	global $dbname;
	global $conn;
	global $owlPDO;
	$arr=Array();
	$list='';
	$option="";
	$str="select distinct kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 and induk!='' and kodeorganisasi in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$_SESSION['empl']['regional']."') order by kodeorganisasi"; #exit("error".$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
		if($bar->tipe=='PT'){
			//skip PT{
			continue;
		}else{
			if($returntype=='array'){
				array_push($arr,$bar->kodeorganisasi);
			}else if($list=='' and $returntype=='list'){
				$list=$bar->kodeorganisasi;
			}else if($list!='' and $returntype=='list') {
				$list.="|".$bar->kodeorganisasi;
			} else{
				$option.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
			}
		}
	}
if($returntype=='array')
        return $arr;
else if($returntype=='list')
        return $list;
else
    return $option;
}
function ambilSubUnit($returntype='array',$induk)
{

        global $dbname;
        global $conn;
        global $owlPDO;
        $arr=Array();
        $list='';
        $option="";
        $str="select distinct kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$induk."' order by kodeorganisasi";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
                if($bar->tipe=='PT')//skip PT
                {
                        continue;
                }
                else
                {
                        if($returntype=='array')
                          array_push($arr,$bar->kodeorganisasi);
                        else if($list=='' and $returntype=='list')
                          {
                                $list=$bar->kodeorganisasi;
                          }
                        else if($list!='' and $returntype=='list') 
                         {
                                $list.="|".$bar->kodeorganisasi;
                         } 
                         else
                         {
                                $option.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
                         }
                }
        }


if($returntype=='array')
        return $arr;
else if($returntype=='list')
        return $list;
else
    return $option;
}


function getVhcCode($returntype='array',$kodeunit)
{

        global $dbname;
        global $conn;
        global $owlPDO;
        $arr=Array();
        $list='';
        $option="";
    $str="select * from ".$dbname.".vhc_5master where kodeorg='".$kodeunit."'
         or kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$kodeunit."')
         order by kodevhc";
        $no=0;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar1=$res->fetch())
        {
                $no+=1;
                $str="select namabarang from ".$dbname.".log_5masterbarang where kodebarang='".$bar1->kodebarang."'";
                $namabarang='';
                $res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res1->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res1->fetch())
                {
                        $namabarang=$bar->namabarang;
                }
                        if($returntype=='array')
                          array_push($arr,$bar1->kodevhc);
                        else if($list=='' and $returntype=='list')
                          {
                                $list=$bar1->kodevhc;
                          }
                        else if($list!='' and $returntype=='list') 
                         {
                                $list.="|".$bar1->kodevhc;
                         } 
                         else
                         {
                                $option.="<option value='".$bar1->kodevhc."'>[".$bar1->kodevhc."]-".$namabarang."</option>";
                         } 
        }	
if($returntype=='array')
        return $arr;
else if($returntype=='list')
        return $list;
else
    return $option;	
}

function getGudangPT($returntype='array',$gudang)
{
        global $dbname;
        global $conn;
        global $owlPDO;
        $arr=Array();
        $list='';
        $option="";
    $str="select distinct kodeorg from ".$dbname.".log_5masterbarangdt where kodegudang='".$gudang."' 
              order by kodeorg";
        $no=0;
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_OBJ);
    while($bar1=$res->fetch())
    {

                $no+=1;
                $str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$bar1->kodeorg."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
                        $namapt=$bar->namaorganisasi;
                }
                        if($returntype=='array')
                          array_push($arr,$bar1->kodeorg);
                        else if($list=='' and $returntype=='list')
                          {
                                $list=$bar1->kodeorg;
                          }
                        else if($list!='' and $returntype=='list') 
                         {
                                $list.="|".$bar1->kodeorg;
                         } 
                         else
                         {
                                $option.="<option value='".$bar1->kodeorg."'>[".$bar1->kodeorg."]-".$namapt."</option>";
                         } 
        }	
if($returntype=='array')
        return $arr;
else if($returntype=='list')
        return $list;
else
    return $option;		
}

function getKegiatanBlok($returntype='array',$blok){
	global $dbname;
	global $conn;
	global $owlPDO;
	$arr=Array();
	$list='';
	$option="";
	$str="select statusblok from ".$dbname.".setup_blok where kodeorg='".$blok."'";
	$no=0;
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res->fetch()){
		$no+=1;
		if($bar1->statusblok=='TM'){
			 $str="select kodekegiatan,kelompok,namakegiatan, noakun as kelakun from ".$dbname.".setup_kegiatan where (kelompok='TM' or kelompok='PNN') and status = '1' order by noakun, kelompok,kodekegiatan";
		}else{
			$str="select kodekegiatan,kelompok,namakegiatan, noakun as kelakun from ".$dbname.".setup_kegiatan where kelompok='".$bar1->statusblok."' and status = '1' order by noakun, kelompok,kodekegiatan"; 
		} 
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
			if($returntype=='array'){
				array_push($arr,$bar1->kodekegiatan);
			}else if($list=='' and $returntype=='list'){
				$list=$bar1->kodekegiatan;
			}else if($list!='' and $returntype=='list') {
				$list.="|".$bar1->kodekegiatan;
			} else{
				$d=$bar->kelakun;
				if($d!=$n){			
					$option.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
				}
					$option.= "<option value='".$bar->kodekegiatan."'>".substr($bar->kodekegiatan,-2)." - ".$bar->namakegiatan."</option>";
				$n=$d;
				if($d!=$n){			
					$optorg.="</optgroup>";
				}
			} 
		}
	}	
	if($returntype=='array'){			
		return $arr;
	}else if($returntype=='list'){			
		return $list;
	}else{
		return $option;		
	}
}

function ambilSeluruhGudang($returntype='array',$kecuali)
{
        global $dbname;
        global $conn;
        global $owlPDO;
        $arr=Array();
        $list='';
        $option="";
        $no=0;
                $no+=1;
                $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
                      where tipe='GUDANG' and kodeorganisasi<>'".$kecuali."' order by namaorganisasi";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar1=$res->fetch())
                {
                        if($returntype=='array')
                          array_push($arr,$bar1->kodeorganisasi);
                        else if($list=='' and $returntype=='list')
                          {
                                $list=$bar1->kodeorganisasi;
                          }
                        else if($list!='' and $returntype=='list') 
                         {
                                $list.="|".$bar1->kodeorganisasi;
                         } 
                         else
                         {
                                $option.= "<option value='".$bar->kodeorganisasi."'>[".$bar->kodeorganisasi."]-".$bar->namaorganisasi."</option>";
                         } 
                }
        if($returntype=='array')
                return $arr;
        else if($returntype=='list')
                return $list;
        else
            return $option;		
}

/**
 * getPostingJabatan
 * Get Jabatan untuk bisa melakukan posting
 * @param	string	$modul		Modul
 * @return 	array				List of Jabatan
 */
function getPostingJabatan($modul) {
        global $dbname;

        $qPosting = selectQuery($dbname,'setup_posting','jabatan',"kodeaplikasi='".$modul."'");
        $tmpPost = fetchData($qPosting);
        $postJabatan = array();
        foreach($tmpPost as $row) {
                $postJabatan[$row['jabatan']] = $row['jabatan'];
        }
        return $postJabatan;
}

/**
 * getDetailTipeMutasi
 * Get detail keterangan tipe transaksi digudang
 * @param	string	$modul		Modul
 * @return 	array				List Keterangan
 */
function getDetailTipeMutasi($tipeid) {
        $hasil = '';
        if($tipeid == '0'){
                $hasil = "Penerimaan Pabrik";
        }else if($tipeid == '1'){
                $hasil = "Masuk";
        }else if($tipeid == '2'){
                $hasil = "Retur Pemakaian";
        }else if($tipeid == '3'){
                $hasil = "Terima Mutasi";
        }else if($tipeid == '5'){
                $hasil = "Pemakaian";
        }else if($tipeid == '6'){
                $hasil = "Retur Mutasi";
        }else{
                $hasil = "Keluar Mutasi";
        }

        return $hasil;
}
function getJabatan($karyawanid){
	global $dbname;
	global $owlPDO;
	$i = makeOption($dbname,'datakaryawan','karyawanid,kodejabatan',"karyawanid='".$karyawanid."'");
	$x = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$i[$karyawanid]."'");
	return $x[$i[$karyawanid]];
}