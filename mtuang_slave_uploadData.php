<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('config/connection.php');

$pemisah=$_POST['pemisah'];
$jenisdata=$_POST['jenisdata'];
$intltiket=$_POST['intltiket'];
$path='tempExcel';

if(is_dir($path))
{
	writeFile($path,$pemisah);
	//chmod($path, 0777);
}
else
{
	if(mkdir($path))
	{
		writeFile($path,$pemisah);
		// chmod($path, 0777);
	}
	else
	{
		echo "<script> alert('Gagal, Can`t create folder for uploaded file');</script>";
		exit(0);
	}
}
 
function writeFile($path,$pemisah)
{
	global $jenisdata;
    $dir=$path;
    $ext=explode('.', basename( $_FILES['filex']['name']));
    $ext=$ext[count($ext)-1];
    $ext=strtolower($ext);
    
	if($ext=='csv')
    {
		$path = $dir."/".date('ymd').".".$ext;
        @unlink($path);
        
		try
		{
			if(move_uploaded_file($_FILES['filex']['tmp_name'], $path))
			{
				$x=readCSV($path,$pemisah);
                simpanData($x,$jenisdata);
			}
		}
		catch(Exception $e)
		{
			echo "<script>alert(\"Error Writing File".addslashes($e->getMessage())."\");</script>";
		}
	}
	else
	{
		echo "<script>alert('Filetype not support');</script>";		 	
	}
}

function simpanData($x,$jenisdata)
{
	global $dbname;
    global $conn;
    global $pemisah;
    global $owlPDO;
    
	$jlhbaris=count($x)-1;
    #baris pertama adalah header;
    foreach($x[0] as $val)
	{
		$header[]=trim($val);
	}
	
	switch ($jenisdata) 
	{
		case 'ACCBAL':
			#ambil noakun
            $str=$owlPDO->query("select noakun from ".$dbname.".keu_5akun where length(noakun)=7");
            $str->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$str->fetch())
			{
				$noakun[]=$bar->noakun;
			}
			
			#periksa kelengkapan data
            if(count($x[0])!=4)
			{
				exit("Error: Form not valid");
			}
			
			foreach($x as $key =>$arr)
			{
				if($key==0)
				{
					continue;
				}
				else
				{
					foreach($arr as $ids =>$rinc)
					{
						if($header[$ids]=='periode' and strlen($rinc)!=6)
						{
							exit("Error: some data on period not valid (line ".$key.")");
						}
						else if($header[$ids]=='noakun' and strlen($rinc)!=7)
						{
							exit("Error: some data on noakun not valid (line ".$key.")");
						}
						else if($header[$ids]=='kodeorg' and strlen($rinc)!=4)
						{
							exit("Error: some data on kodeorg not valid (line ".$key.$rinc.")");
						}
						else if($header[$ids]=='noakun' )
						{
							#periksa noakun yang disubmit
							$akunbermasalah[$rinc]=$rinc;
							foreach($noakun as $bb=>$cc)
							{
								if($cc==$rinc)
									unset($akunbermasalah[$rinc]);
							}
						}
					}
				}
			}
			
			if(count($akunbermasalah)>0)
			{
				echo "The following account number were not defined:<br>";
				print_r($akunbermasalah);
				exit();
			}
			
			#ambil  kolom periode
            foreach ($header as $ki=> $val)
			{
				if($val=='periode')
				{
					$index=$ki;
				}
				
                if($val=='kodeorg')
				{
					$idkOrg=$ki;
                }
			}
			
			$column='awal'.substr($x[1][$index],4,2);
            foreach($header as $ki=>$val)
			{
				if($val=='saldo')
				{
					$header[$ki]=$column;
                    $indexNumeric=$ki;
				}
			}
			
			#delete first
			$str="delete from ".$dbname.".keu_saldobulanan where kodeorg='".$x[1][$idkOrg]."' and periode='".$x[1][$index]."'";
            try
			{
				$owlPDO->exec($str);        
			}
			catch (PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "<br/>";
                die();
			}
			
			#generate SQL:
            $stringSQL="insert into ".$dbname.".keu_saldobulanan(";
            foreach ($header as $ki=> $val)
			{
				if($ki==0)
					$stringSQL.=$val;
				else
					$stringSQL.=",".$val;                      
			}
			
			$stringSQL.=") values";
            foreach($x as $key =>$arr)
			{
				if($key==0)
				{
					continue;
				}
				else
				{
					foreach($arr as $ki=>$val)
					{
						if($ki==0)
						{
							if($key==1)
							{
								$stringSQL.="('".trim($val)."'";
							}
							else
							{
								$stringSQL.=",('".trim($val)."'";
							}
						}
						else
						{
							$stringSQL.=",'".trim($val)."'";
						}
					}
					
					$stringSQL.=")";
				}
			}
			
			$stringSQL.=";";
			try
			{
				$suc=$owlPDO->exec($stringSQL);
                if($suc)
				{
					echo "Uploaded";
				}
			}
			catch (PDOException $e)
			{
				print " Gagal  !: " . $e->getMessage() . "<br/>";
				die();
			}
		break;
		
		
#===========================================================end  prevbal ======================================================             
           case 'JOURNAL':  
              #ambil noakun
              $str=$owlPDO->query("select noakun from ".$dbname.".keu_5akun where length(noakun)=7");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $noakun[]=$bar->noakun;
              }
              #ambil  nik
              $str=$owlPDO->query("select karyawanid from ".$dbname.".datakaryawan");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $nik[]=$bar->karyawanid;
              }
              #ambil  kegiatan
              $str=$owlPDO->query("select kodekegiatan from ".$dbname.".setup_kegiatan");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $kegiatan[]=$bar->kodekegiatan;
              }
              #ambil  supplier
              $str=$owlPDO->query("select supplierid from ".$dbname.".log_5supplier");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $supplier[]=$bar->supplierid;
              }
              #ambil  custommer
              $str=$owlPDO->query("select kodecustomer  from ".$dbname.".pmn_4customer");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $custommer[]=$bar->kodecustomer;
              }
              
              #ambil  blok
              $str=$owlPDO->query("select kodeorg  from ".$dbname.".setup_blok");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $blok[]=$bar->kodeorg;
              }
              
              #periksa kelengkapan data
              $zz=0;
          foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{                 
                  foreach($arr as $ids =>$rinc){
                      $x[$key][$ids]=trim($rinc);
                      if($header[$ids]=='tanggal'){
                          $rinc=str_replace('-','',$rinc);
                          $rinc=str_replace('/','',$rinc);
                          if(strlen($rinc)!=8){
                            exit("Error: some data on date not valid (line ".$key.")");
                          }
                          else if(substr($rinc,0,4)<'2000'){
                              exit("Error: date not valid (line ".$key.")");
                          }
                          else
                            $x[$key][$ids]=$rinc;
                      }
                      if($header[$ids]=='noakun' and strlen($rinc)!=7 and $rinc!='0'){
                          exit("Error: some data on noakun not valid (line ".$key.")");
                      }
                      if($header[$ids]=='kodeorg' and strlen($rinc)!=4){
                          exit("Error: some data on kodeorg not valid (line ".$key.")");
                      }
                      if($header[$ids]=='matauang'){
                          if(trim($rinc)=='')
                          exit("Error: some data on currency not valid (line ".$key.")");
                      }                      
                      if($header[$ids]=='kurs'){
                          if(trim($rinc)=='')
                            $x[$key][$ids]=1;
                      }  
                      if($header[$ids]=='nourut'){
                          if(trim($rinc)=='')
                            $x[$key][$ids]=$zz++;
                      }  
                      
                      if($header[$ids]=='noakun' ){
                        #periksa noakun yang disubmit
                        $akunbermasalah[$rinc]=$rinc;
                        foreach($noakun as $bb=>$cc){
                                if($cc==$rinc or trim($rinc)=='0')
                                    unset($akunbermasalah[$rinc]);
                        }
                      }
                      
                      if($header[$ids]=='nik'  and trim($rinc)!=''){
                        #periksa nik yang disubmit
                        $nikbermasalah[$rinc]=$rinc;
                        foreach($nik as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($nikbermasalah[$rinc]);
                        }
                      }
                       if($header[$ids]=='kodekegiatan'   and trim($rinc)!=''){
                        $kegiatanbermasalah[$rinc]=$rinc;
                        foreach($kegiatan as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($kegiatanbermasalah[$rinc]);
                        }
                      }                     
                        if($header[$ids]=='kodesupplier'   and trim($rinc)!=''){
                        $supplierbermasalah[$rinc]=$rinc;
                        foreach($supplier as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($supplierbermasalah[$rinc]);
                        }
                      }                        
            
                        if($header[$ids]=='kodecustomer'   and trim($rinc)!=''){
                        $custommerbermasalah[$rinc]=$rinc;
                        foreach($custommer as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($custommerbermasalah[$rinc]);
                        }
                      }                         
            
                        if($header[$ids]=='kodeblok'   and trim($rinc)!=''){
                        $blokbermasalah[$rinc]=$rinc;
                        foreach($blok as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($blokbermasalah[$rinc]);
                        }
                      }                        
                  }
              }
          }
          $bermasalah=false;
          if(count($akunbermasalah)>0){
              echo "The folowing account number were not defined:<br>";
              print_r($akunbermasalah);
              $bermasalah=true;
          }
          if(count($nikbermasalah)>0){
              echo "The folowing NIK were not defined:<br>";
              print_r($nikbermasalah);
              $bermasalah=true;
          }
          if(count($kegiatanbermasalah)>0){
              echo "The folowing activity code were not defined:<br>";
              print_r($kegiatanbermasalah);
              $bermasalah=true;
          }
          if(count($supplierbermasalah)>0){
              echo "The folowing supplier/contractor code were not defined:<br>";
              print_r($supplierbermasalah);
              $bermasalah=true;
          }
          if(count($custommerbermasalah)>0){
              echo "The folowing custommer code were not defined:<br>";
              print_r($custommerbermasalah);
              $bermasalah=true;
          }
          if(count($blokbermasalah)>0){
              echo "The folowing block code were not defined:<br>";
              print_r($blokbermasalah);
              $bermasalah=true;
          }
        if($bermasalah){
            exit();
        }
     #periksa jumlah debet dan kredit
         foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{                 
                  foreach($arr as $ids =>$rinc){
                        if($header[$ids]=='jumlah'){
                            $total+=$rinc;
                            $tt+=abs($rinc);
                        }
                  } 
              }
         }
         $tdecre=$tt/2;
         if(abs($total)>100){
             exit("Error:Total amount not balance:".$total);
         }
     #create header journal
                    #ambil  kolom periode
              foreach ($header as $ki=> $val){
                if($val=='tanggal'){
                    $itanggal=$ki;
                  }
                if($val=='nojurnal'){
                    $inojurnal=$ki;
                }
                if($val=='kurs'){
                    $ikurs=$ki;
                }
                 if($val=='matauang'){
                    $imatauang=$ki;
                }           
                  if($val=='noreferensi'){
                    $inoreferensi=$ki;
                }                   
              }   
      
//              #delete first
              $str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$x[2][$inojurnal]."'";
                try{
                          $owlPDO->exec($str);        
                  }
                catch (PDOException $e) {
                           print " Gagal  !: " . $e->getMessage() . "<br/>";
                           die();
                    }

#generate header journal
         $str="insert into ".$dbname.".keu_jurnalht (`nojurnal`, `kodejurnal`, `tanggal`, `tanggalentry`, `posting`, `totaldebet`, `totalkredit`, `amountkoreksi`, `noreferensi`, `autojurnal`, `matauang`, `kurs`, `revisi`) VALUES
                    ('".$x[2][$inojurnal]."', 'Hist', '".$x[2][$itanggal]."', '".date('Ymd')."', 1,".$tdecre.", -".$tdecre.", '0', '".$x[2][$inoreferensi]."', 1, '".$x[2][$imatauang]."', ".$x[2][$ikurs].", 0);";
                 try{
                          $owlPDO->exec($str);        
                  }
                catch (PDOException $e) {
                           print " Gagal  ! insert header: ".$str . $e->getMessage() . "<br/>";
                           die();
                    }                
//              #generate detail SQL:
              $stringSQL="insert into ".$dbname.".keu_jurnaldt(";
              foreach ($header as $ki=> $val){
                  if($ki==0)
                     $stringSQL.=$val;
                  else
                     $stringSQL.=",".$val;                      
              }
              $stringSQL.=") values";
               foreach($x as $key =>$arr){
                    if($key==0){
                        continue;
                    }else{
                            foreach($arr as $ki=>$val){
                                if($ki==0){
                                    if($key==1){
                                        $stringSQL.="('".trim($val)."'";
                                    }
                                    else{
                                        $stringSQL.=",('".trim($val)."'";
                                    }
                                }else{
                                    $stringSQL.=",'".trim($val)."'";
                                }
                            }
                            $stringSQL.=")";
                    }
               }
               $stringSQL.=";";
                try{
                          $owlPDO->exec($stringSQL);       
                          echo "uploaded";
                  }
                catch (PDOException $e) {
                           print " Gagal  !, insert detail : " . $e->getMessage() . "<br/>";
                           die();
                    }
              break;
 #===========================================================end  prevbal ======================================================             
  #==============================BEGIN MATERIAL
              case 'INV':  

              #ambil  blok
              $str=$owlPDO->query("select kodebarang  from ".$dbname.".log_5masterbarang");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $barang[]=$bar->kodebarang;
              }
              #ambil kodeorganisasi
              $str=$owlPDO->query("select kodeorganisasi  from ".$dbname.".organisasi");
              $str->setFetchMode(PDO::FETCH_OBJ);
              while($bar=$str->fetch()){
                  $org[]=$bar->kodeorganisasi;
              }
              #periksa kelengkapan data
              $zz=0;
          foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{                 
                  foreach($arr as $ids =>$rinc){
                      $x[$key][$ids]=trim($rinc);                     
                  }
              }
          }
              foreach ($header as $ki=> $val){
                if($val=='periode'){
                    $iperiode=$ki;
                  }
                if($val=='kodeorg'){
                    $ikodeorg=$ki;
                }
                if($val=='kodebarang'){
                    $ikodebarang=$ki;
                }
                 if($val=='saldoakhirqty'){
                    $isaldoakhirqty=$ki;
                }           
                  if($val=='hargarata'){
                    $ihargarata=$ki;
                }                   
                  if($val=='kodegudang'){
                    $ikodegudang=$ki;
                }                    
              }   

  #periksa periode gudang
          $str=$owlPDO->query("select periode from ".$dbname.".setup_periodeakuntansi where periode='".$x[1][$iperiode]."' and
                    kodeorg='".  $x[1][$ikodegudang]."' and tutupbuku=0");
          $str->setFetchMode(PDO::FETCH_OBJ);
          $numrows=owlBaris($str);
          if($numrows<1){
              exit("Error: Accounting period for ".$x[1][$ikodegudang]." not defined");
          }
        #periksa pt apakah terdaftar atau tidak  
          $kodept=false;
          foreach($org as $bb=>$vb){
              if($x[1][$ikodeorg]==$vb){
                  $kodept=true;
              }    
          }
          if(!$kodept){
              exit("Error : Company code not found");
          }
          #periksa kode barang
            foreach($x as $key =>$arr){
                if($key==0){
                    continue;
                }else{             
                       $arrkodemasalah[$arr[$ikodebarang]]=$arr[$ikodebarang];
                       foreach($barang as $tt =>$gh){
                           if($arr[$ikodebarang]==$gh){
                               unset($arrkodemasalah[$arr[$ikodebarang]]);
                           }                          
                    }
                    if($arr[$ihargarata]=='0'){
                        $hargamasalah[$arr[$ikodebarang]]=$arr[$ikodebarang];
                    }
                    if($arr[$isaldoakhirqty]<='0'){
                        $qtymasalah[$arr[$isaldoakhirqty]]=$arr[$isaldoakhirqty];
                    }                    
                }
            }    
            if(count($arrkodemasalah)>0){
                echo" The folowing material code not defined on material master:";
                print_r($arrkodemasalah);
                exit();
            }
            else if(count($hargamasalah)>0){
                echo" The folowing material with blank price";
                print_r($hargamasalah);
                exit();                
            }
            else if(count($qtymasalah)>0){
                echo" The folowing material with blank qty";
                print_r($qtymasalah);
                exit();                
            }            
                       
      
//              #delete first
              $str="delete from ".$dbname.".log_5saldobulanan where periode='".$x[1][$iperiode]."' and kodegudang='".$x[1][$ikodegudang]."'";
                try{$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>";die();}
               $str="delete from ".$dbname.".log_5masterbarangdt where  kodegudang='".$x[1][$ikodegudang]."'";
               try{$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>";die();}
              
              
#generate sql
         $stringSQL="insert into ".$dbname.".log_5saldobulanan(kodeorg,kodebarang,saldoakhirqty,hargarata,lastuser,periode,
                   nilaisaldoakhir,kodegudang,saldoawalqty,hargaratasaldoawal,nilaisaldoawal) values";
         $stringSQL1="insert into ".$dbname.".log_5masterbarangdt(kodeorg,kodebarang,saldoqty,hargalastin,
                     hargalastout,lastuser,kodegudang) values";

               foreach($x as $key =>$arr){
                    if($key==0){
                        continue;
                    }else{                  
                            if($key=='1'){
                                $stringSQL.="('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."',0,'".$arr[$iperiode]."',
                                                        '".($arr[$isaldoakhirqty]*$arr[$ihargarata])."','".$arr[$ikodegudang]."','".$arr[$isaldoakhirqty]."',
                                                            '".$arr[$ihargarata]."','".($arr[$isaldoakhirqty]*$arr[$ihargarata])."')";
                                
                                $stringSQL1.="('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."','".$arr[$ihargarata]."',0,
                                                       '".$arr[$ikodegudang]."')";
                            }else{
                                $stringSQL.=",('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."',0,'".$arr[$iperiode]."',
                                                        '".($arr[$isaldoakhirqty]*$arr[$ihargarata])."','".$arr[$ikodegudang]."','".$arr[$isaldoakhirqty]."',
                                                            '".$arr[$ihargarata]."','".($arr[$isaldoakhirqty]*$arr[$ihargarata])."')";    
                                $stringSQL1.=",('".$arr[$ikodeorg]."','".$arr[$ikodebarang]."','".$arr[$isaldoakhirqty]."','".$arr[$ihargarata]."','".$arr[$ihargarata]."',0,
                                                       '".$arr[$ikodegudang]."')";                                
                            }
                    }
               }
               $stringSQL.=";";
               $stringSQL1.=";";          
               try{$owlPDO->exec($stringSQL);} catch (PDOException $e) {print " Gagal  ! insert saldobulanan: " . $e->getMessage() . "<br/>";die();}
               try{$owlPDO->exec($stringSQL1); echo "Uploaded";} catch (PDOException $e) {print " Gagal  ! insert masterbarangdt: " . $e->getMessage() . "<br/>";die();}
              break;                          
           #====================================================END INV  ================================================ 
      case 'PO':  
      #ambil  supplier
      $str="select supplierid from ".$dbname.".log_5supplier";
	  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	  $res->setFetchMode(PDO::FETCH_OBJ);
      while($bar=$res->fetch()){
          $supplier[]=$bar->supplierid;
      }
      #ambil  kodeorganisasi
      $str=$owlPDO->query("select kodeorganisasi from ".$dbname.".organisasi where tipe='PT'");
      $str->setFetchMode(PDO::FETCH_OBJ);
      while($bar=$str->fetch()){
          $kodept[]=$bar->kodeorganisasi;
      }
      #ambil  kodeorganisasi
      $str=$owlPDO->query("select kodebarang from ".$dbname.".log_5masterbarang");
      $str->setFetchMode(PDO::FETCH_OBJ);
      while($bar=$str->fetch()){
          $kdbarang[]=$bar->kodebarang;
      }
        foreach($x as $key =>$arr){
              if($key==0){
                  continue;
              }else{
                  foreach($arr as $ids =>$rinc){
                      if($header[$ids]=='kodeorg' and strlen($rinc)!=3){
                          exit("Error: some data on kodeorg not ".$x[$key][$ids]."___".$ids."__".$key." valid (line ".$key.")");
                       }
                       if($header[$ids]=='matauang'){
                              if(trim($rinc)==''){
                                exit("Error: some data on currency not valid (line ".$key.")");
                              }
                       } 
                       if($header[$ids]=='kodesupplier'){     
                            $supplierbermasalah[$rinc]=$rinc;
                            foreach($supplier as $bb=>$cc){
                                    if($cc==$rinc)
                                        unset($supplierbermasalah[$rinc]);
                             }
                        }  
                       if($header[$ids]=='kodeorg' ){
                        #periksa kodeblok yang disubmit
                        $kdptbermasalah[$rinc]=$rinc;
                        foreach($kodept as $bb=>$cc){
                                if($cc==$rinc)
                                    unset($kdptbermasalah[$rinc]);
                        }
                      }
                      if($header[$ids]=='kodebarang'   and trim($rinc)!=''){
                            $kdbarangbermasalah[$rinc]=$rinc;
                            foreach($kdbarang as $bb=>$cc){
                                    if($cc==$rinc)
                                        unset($kdbarangbermasalah[$rinc]);
                             }
                      }
                      if($header[$ids]=='kurs'){
                          if(trim($rinc)=='')
                            $x[$key][$ids]=1;
                      }  
                      if($header[$ids]=='tanggal'){
                              $rinc=str_replace('-','',$rinc);
                              if(strlen($rinc)!=8){
                                exit("Error: some data on date not valid (line ".$key.":".$rinc.")");
                              }
                              else if(substr($rinc,0,4)<'2000'){
                                  exit("Error: date not valid (line ".$key.")");
                              }
                              
                      }

                  }
                  
              }
        }
          if(count($supplierbermasalah)>0){
              echo "The following supplier/contractor code on were not defined:<br>";
              echo"<pre>";
              print_r($supplierbermasalah);
              echo"</pre>";
              exit();
          }
          if(count($kdptbermasalah)>0){
              echo "The following company code were not defined:<br>";
              echo"<pre>";
              print_r($kdptbermasalah);
              echo"</pre>";
              exit();
          }
           if(count($kdbarangbermasalah)>0){
              echo "The following material code were not defined:<br>";
              echo"<pre>";
              print_r($kdbarangbermasalah);
              echo"</pre>";
              exit();
          }
          
        $jmhrBrs=count($x[0]);
        $jmlhRow=count($x);
        
        $aer=0;
        foreach($x[0] as $lstDt=>$lstNama){
           if($aer==0){
                $sinsHed.="insert into ".$dbname.".log_poht (`".trim($lstNama)."`";
                $aet=0;
                $nopo=$lstNama;
            }else{
                if($aer<11){
                    $sinsHed.=",`".trim($lstNama)."`";
                }else{
                    if($aet==0){
                        $sinsHed.=",`statuspo`,`stat_release`,`lokalpusat`) values ";
                        $sInsDet.="insert into ".$dbname.".log_podt (`".trim($nopo)."`,`".trim($lstNama)."`";
                    }else{
                        if($aet<4){
                            $sInsDet.=",`".trim($lstNama)."`";
                        }
                    }
                    $aet++;
                }
            }
            if($aer<11){
                $aer++;
            } 
            if($aet==4){
                  $sInsDet.=",`harganormal`,`hargasbldiskon`)  ";
            }
        }
       
        for($aerto=1;$aerto<$jmlhRow;$aerto++){
                if($nopohead!=$x[$aerto][0]){
                    $nopohead="";
                    $headUtm="";
                    $headUtm.=$sinsHed;
                    $nopohead=$x[$aerto][0];
                    $scek=$owlPDO->query("select * from ".$dbname.".log_poht where nopo='".$nopohead."'");
                    $scek->setFetchMode(PDO::FETCH_OBJ);
                    $numrows=owlBaris($scek);
                    if($numrows<1){
                            $headUtm.="('".trim($x[$aerto][0])."','".trim($x[$aerto][1])."','".trim($x[$aerto][2])."','".trim($x[$aerto][3])."','".trim($x[$aerto][4])."','".trim($x[$aerto][5])."','".trim($x[$aerto][6])."','".trim($x[$aerto][7])."','".trim($x[$aerto][8])."','".trim($x[$aerto][9])."','".trim($x[$aerto][10])."','2','1','".trim($x[$aerto][15])."')";
                            $tes=$owlPDO->exec($headUtm);
                            if(!$tes){
                                exit("error:\n".$headUtm."__l");
                            }else{
                                $detData="";
                                $detData.=$sInsDet." values ";
                                $hrgdis[$aerto]=trim($x[$aerto][14]);
                                if(intval($x[$aerto][6])!=0){
                                    $hrgdis[$aerto]=floatval($x[$aerto][14])-(floatval($x[$aerto][14])*(floatval($x[$aerto][6])/100));
                                }
                                $sDelDt="delete from ".$dbname.".log_podt where nopo='".trim($x[$aerto][0])."' and kodebarang='".trim($x[$aerto][11])."'";
                                $tes=$owlPDO->exec($sDelDt);
                                if($tes){
                                    $detData.="('".trim($x[$aerto][0])."','".trim($x[$aerto][11])."','".trim($x[$aerto][12])."','".trim($x[$aerto][13])."','".$hrgdis[$aerto]."','".$hrgdis[$aerto]."','".trim($x[$aerto][14])."')";
                                    $tes=$owlPDO->exec($detData);
                                    if(!$tes){
                                      exit("error:\n".$detData."__uatas");
                                    }
                                    else{ echo "Uploaded";}
                                }
                            }
                    }else{
                        $sdel="delete from ".$dbname.".log_poht where nopo='".$x[$aerto][0]."'";
                        $tes=$owlPDO->exec($sdel);
                        if($tes){
                            $headUtm.="('".trim($x[$aerto][0])."','".trim($x[$aerto][1])."','".trim($x[$aerto][2])."','".trim($x[$aerto][3])."','".trim($x[$aerto][4])."','".trim($x[$aerto][5])."','".trim($x[$aerto][6])."','".trim($x[$aerto][7])."','".trim($x[$aerto][8])."','".trim($x[$aerto][9])."','".trim($x[$aerto][10])."','2','1','".trim($x[$aerto][15])."')";
                            $tes=$owlPDO->exec($headUtm);
                            if(!$tes){
                                exit("error:\n".$headUtm."__s");
                            }else{
                                $detData="";
                                $detData.=$sInsDet." values ";
                                $hrgdis[$aerto]=trim($x[$aerto][14]);
                                if(intval($x[$aerto][6])!=0){
                                    $hrgdis[$aerto]=floatval($x[$aerto][14])-(floatval($x[$aerto][14])*(floatval($x[$aerto][6])/100));
                                }
                                $sDelDt="delete from ".$dbname.".log_podt where nopo='".trim($x[$aerto][0])."' and kodebarang='".trim($x[$aerto][11])."'";
                                $tes=$owlPDO->exec($sDelDt);
                                if($tes){
                                    $detData.="('".trim($x[$aerto][0])."','".trim($x[$aerto][11])."','".trim($x[$aerto][12])."','".trim($x[$aerto][13])."','".$hrgdis[$aerto]."','".$hrgdis[$aerto]."','".trim($x[$aerto][14])."')";
                                    $tes=$owlPDO->exec($detData);
                                    if(!$tes){
                                      exit("error:\n".$detData."__t");
                                    } else{ echo "Uploaded";}
                                }
                            }
                        }
                    }
                }else{
                    $detData="";
                    $detData.=$sInsDet." values ";
                    $hrgdis[$aerto]=trim($x[$aerto][14]);
                    if(intval($x[$aerto][6])!=0){
                        $hrgdis[$aerto]=floatval($x[$aerto][14])-(floatval($x[$aerto][14])*(floatval($x[$aerto][6])/100));
                    }
                    $sDelDt="delete from ".$dbname.".log_podt where nopo='".trim($x[$aerto][0])."' and kodebarang='".trim($x[$aerto][11])."'";
                    $tes=$owlPDO->exec($sDelDt);
                    if($tes){
                        $detData.="('".trim($x[$aerto][0])."','".trim($x[$aerto][11])."','".trim($x[$aerto][12])."','".trim($x[$aerto][13])."','".$hrgdis[$aerto]."','".$hrgdis[$aerto]."','".trim($x[$aerto][14])."')";
                        $tes=$owlPDO->exec($detData);
                        if(!$tes){
                          exit("error:\n".$detData."__u1");
                        } else{ echo "Uploaded";}
                    }   
              }
                
        }
       
      break;
		#  ====================================================END PO=====================================
		
		
		#====================================================START ABSENSI================================================ 
		case 'ABSENSI': 
		
			$nopoisi=0;
			
			$str="select nik,karyawanid,subbagian,lokasitugas from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$karid[$bar['nik']]=$bar['karyawanid'];
				$subbagian[$bar['nik']]=$bar['subbagian'];
				$loktgs[$bar['nik']]=$bar['lokasitugas'];
			}
			
			$str="select kodeabsen, keterangan from ".$dbname.".sdm_5absensi";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$tipeabsen[$bar['kodeabsen']]=$bar['kodeabsen'];
				$ketabsen[$bar['kodeabsen']]=$bar['keterangan'];
			}
			
			foreach($x as $key =>$arr)
			{
				if($key==0)
				{
					continue;
				}
				else
				{
					foreach($arr as $ids =>$rinc)
					{
						if($nopoisi!=1)
						{
							$nopoisi=1;
						}
					}
				}
			}
			
			$jmlhRow=count($x);
			$key=1;
			
			for($row=1;$row<$jmlhRow;$row++)
			{
				$rowerr=$row+1;
				
				//TANGGAL
				if($x[$row][2]<1 || $x[$row][2]>31)
				{
					exit("Error : Tanggal yang diizinkan hanya 1 sd 31 [cek pada baris ".$rowerr."]");
				}
				
				if(strlen($x[$row][2])==1)
				{
					$x[$row][2]='0'.$x[$row][2];
				}
				else if(strlen($x[$row][2])==2)
				{
					$x[$row][2]=$x[$row][2];
				}
				
				//BULAN  
				if($x[$row][1]<1 || $x[$row][1]>12)
				{
					exit("Error : Bulan yang diizinkan hanya 1 sd 12 [cek pada baris ".$rowerr."]");
				}
				
				if($x[$row][0]<2015 || $x[$row][0]>2100)
				{
					exit("Error : Tahun yang diizinkan hanya 2015 sd 2100 [cek pada baris ".$rowerr."]");
				}
				
				if(strlen($x[$row][1])==1)
				{
					$x[$row][1]='0'.$x[$row][1];
				}
				else if(strlen($x[$row][1])==2)
				{
					$x[$row][1]=$x[$row][1];
				}
				
				if($karid[$x[$row][3]]=='')
				{
					exit("Warning : Karyawan dengan nik : ".$x[$row][3]." tidak ada. [cek pada baris ".$rowerr."] ");
				}
				
				if($subbagian[$x[$row][3]]=='')
				{
					$subbagian[$x[$row][3]]=$loktgs[$x[$row][3]];
				}
				
				if($tipeabsen[$x[$row][5]]=='')
				{
					echo "<link rel=stylesheet type=text/css href=style/generic.css>";
					echo "Kode absensi : [ ".$x[$row][5]." ] tidak ada. [cek pada baris ".$rowerr."], kode yang tersedia sebagai berikut : <br><br>";
					echo"<table cellspacing='1' border='0' class='sortable'>
						<thead>
						<tr class=rowheader>
							<td align=center>No</td>
							<td align=center>".$_SESSION['lang']['jenisabsensi']."</td>
							<td align=center>".$_SESSION['lang']['keterangan']."</td>
						</tr>
						</thead>";
					
					$nomor=0;
					foreach($tipeabsen as $kdabs)
					{
						$nomor+=1;
						echo"<tr class=rowcontent>
							<td align=center>".$nomor."</td>
							<td align=center>".$kdabs."</td>
							<td>".$ketabsen[$kdabs]."</td>
						</tr>";
					}
					
					echo"</table>";
					exit();
				}
				
				$cektgl=$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2];
				if($cektgl=='0000-00-00')
				{
					exit("Error : Format tanggal ada yang salah.");
				}
				
				## cek periode di HT
				$str="SELECT periode FROM `sdm_5periodegaji`
				where kodeorg='".substr($subbagian[$x[$row][3]],0,4)."' and tanggalsampai >= '".$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2]."' order by periode asc limit 1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();	
				$perht=$bar['periode'];
				
				#cek apakah sudah tutup buku gaji dan keuangan di periode tsb
				$str=" SELECT count(*) as jumlah FROM `sdm_5periodegaji`
					where kodeorg='".substr($subbagian[$x[$row][3]],0,4)."' and periode='".$perht."' and sudahproses=1 and jenisgaji='H'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();	
				$ttpgj=$bar['jumlah'];
				if($ttpgj>0)
				{
					exit("Warning : Periode gaji : ".$perht." sudah ditutup. [cek pada baris ".$rowerr."] ");
				}
				
				$str=" SELECT count(*) as jumlah FROM `setup_periodeakuntansi` where kodeorg='".substr($subbagian[$x[$row][3]],0,4)."' and periode='".$perht."' and tutupbuku=1";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();	
				$ttpkeu=$bar['jumlah'];
				if($ttpgj>0)
				{
					exit("Warning : Periode akuntansi : ".$perht." sudah ditutup.  [cek pada baris ".$rowerr."] ");
				}
				$optOrg = makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$loktgs[$x[$row][3]]."'");
				
				if($optOrg[$loktgs[$x[$row][3]]] == 'PABRIK'){				
					#delete HT
					$str="delete from ".$dbname.".sdm_absensiht where kodeorg='".$subbagian[$x[$row][3]]."' and tanggal='".$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2]."' ";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
					
					#insert HT
					$str="insert into ".$dbname.".sdm_absensiht (kodeorg,tanggal,periode) values";
					$str.=" ('".$subbagian[$x[$row][3]]."','".$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2]."','".$perht."')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
					
					#delete dt
					$str="delete from ".$dbname.".sdm_absensidt where kodeorg='".$subbagian[$x[$row][3]]."' and karyawanid='".$karid[$x[$row][3]]."' and tanggal='".$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2]."' ";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e)
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
					
					#insert dt 
					$str="insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,shift,absensi,jam,jamPlg,penjelasan,fingerprint) values";
					$str.=" ('".$subbagian[$x[$row][3]]."','".$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2]."','".$karid[$x[$row][3]]."','".$x[$row][4]."','".$x[$row][5]."','".trim($x[$row][6])."','".trim($x[$row][7])."','".trim($x[$row][8])."','1')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				else
				{
					#delete
					$str="delete from ".$dbname.".upload_absensi where kodeorg='".$subbagian[$x[$row][3]]."' and karyawanid='".$karid[$x[$row][3]]."' and tanggalabsen='".$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2]."' ";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e)
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
					
					#insert dt 
					$str="insert into ".$dbname.".upload_absensi (kodeorg,tanggalabsen,karyawanid,absensi,jam,jampulang,sumber,flag,tanggalinput,userid) values";
					$str.=" ('".$subbagian[$x[$row][3]]."',
						'".$x[$row][0].'-'.$x[$row][1].'-'.$x[$row][2]."',
						'".$karid[$x[$row][3]]."',
						'".$x[$row][5]."',
						'".trim($x[$row][6])."',
						'".trim($x[$row][7])."',
						'upload',
						'0',
						'".date("Y-m-d")."',
						'".$_SESSION['standard']['userid']."')";
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
			}//tutup for
			
			echo "Notice : Data sudah berhasil diupload.";
			break; 

      
      
      #====================================================START HARGA HARIAN PASAR================================================ 

      case 'HARGAHARIANPASAR':  
      
       
      $nopoisi=0;
      foreach($x as $key =>$arr){
          if($key==0){
              continue;
          }else{                 
              foreach($arr as $ids =>$rinc){
                     if($nopoisi!=1){
                          $nopoisi=1;
                         
                        if($header[2]=='tanggal')
                        {
                                  
                            if(strlen($x[$key][2])==1)
                            {
                              $x[$key][2]='0'.$x[$key][2];
                            }
                            else if(strlen($x[$key][2])==2)
                            {
                                $x[$key][2]=$x[$key][2];
                            }      
                           
                        }
                          
                     }
                       
              }
         }//else
      }//foreach  
      
      $jmlhRow=count($x);
	  
      $key=1;
       for($ind=1;$ind<$jmlhRow;$ind++){
          $w="insert into ".$dbname.".pmn_hargapasar (tanggal,kodeproduk,pasar,satuan,harga,matauang,statusharga,ffa,mni) values";
          $w.=" ('".$x[$ind][0].'-'.$x[$ind][1].'-'.$x[$ind][2]."','".$x[$ind][3]."','".$x[$ind][4]."','".$x[$ind][5]."',";
          $w.="'".trim($x[$ind][6])."','".trim($x[$ind][7])."','".trim($x[$ind][8])."','".trim($x[$ind][9])."','".trim($x[$ind][10])."')";
        try{
            $owlPDO->exec($w);
          }
          catch (PDOException $e) {
                    exit(" Gagal :".$e->getMessage());
        }
		  
      }
      echo "Notice: Data berhasil diupload";
      break; 
   
   
	case 'TIMBANGANPEMBELI':  
      
   
      $nopoisi=0;
	  
	  
		// $str="select nik,karyawanid,subbagian,lokasitugas from ".$dbname.".datakaryawan where lokasitugas='".$_SESSION['empl']['lokasitugas']."' ";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $karid[$bar['nik']]=$bar['karyawanid'];
			// $subbagian[$bar['nik']]=$bar['subbagian'];
			// $loktgs[$bar['nik']]=$bar['lokasitugas'];
		// }
	  
	
	  
      foreach($x as $key =>$arr){
          if($key==0){
              continue;
          }else{     
              foreach($arr as $ids =>$rinc){
				  
				 $nokontrak=$arr[1]; 
				  
				 if($nopoisi!=1){
					  $nopoisi=1; 
				 }    
              }
         }
      }

	$str="select notransaksi from ".$dbname.".pabrik_timbangan where nokontrak='".$nokontrak."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$notran[$bar['notransaksi']]=$bar['notransaksi'];
	}
	  

	 
      $jmlhRow=count($x);
      $key=1;
       for($row=1;$row<$jmlhRow;$row++){
		   $rowerr=$row+1;
			
				
		   if($notran[$x[$row][0]]!=$x[$row][0]){
			   exit("Warning : Tiket dengan nomor : ".$x[$row][0]." tidak ada. dinomor kontrak ".$nokontrak." [cek pada baris ".$rowerr."] ");
		   }
		   
			
			$str="update ".$dbname.".pabrik_timbangan set kgpembeli='".$x[$row][2]."' where notransaksi='".$x[$row][0]."' and nokontrak='".$x[$row][1]."'";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e) {
			   print " Gagal  !: " . $e->getMessage() . "\n"; 
			   die(); 
			}
		   
			
      }//tutup for
      echo "Notice : Data sudah berhasil diupload.";
      break; 
	  
	  case 'PENERIMAANTBSRAMP': 
	  $nopoisi=0;
      foreach($x as $key =>$arr){
          if($key==0){
              continue;
          }else{                 
              foreach($arr as $ids =>$rinc){
                     if($nopoisi!=1){
                          $nopoisi=1;
                         
                        if($header[2]=='tanggal')
                        {
                                  
                            if(strlen($x[$key][2])==1)
                            {
                              $x[$key][2]='0'.$x[$key][2];
                            }
                            else if(strlen($x[$key][2])==2)
                            {
                                $x[$key][2]=$x[$key][2];
                            }      
                           
                        }
                          
                     }
                       
              }
         }//else
      }//foreach  
      
      $jmlhRow=count($x);
	  
		$key=1;
		for($ind=1;$ind<$jmlhRow;$ind++){
      if($ind==1){
        $tgl = substr(trim($x[$ind][5]),0,4)."-".substr(trim($x[$ind][5]),4,2)."-".substr(trim($x[$ind][5]),6,2);
        #hapus dulu sebelum insert
        // $sDel="delete from ".$dbname.".pmn_penerimaantbsramp where kodesupplier='".$x[$ind][3]."' and left(datein,10)='".$tgl."'";
        // $owlPDO->exec($sDel);
      }
			##Get No Tiket
			$intltiket = $_POST['intltiket'];
			$str = "select * from ".$dbname.".pmn_penerimaantbsramp where notiket like '".$intltiket."%' order by notiket desc limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$notiket = $bar['notiket'];
			
			if($notiket=='')
			{
				$notiket = $intltiket."000001";
			}
			else
			{
				$notiket = str_replace($intltiket,'',$notiket);
				$notiket = $intltiket.addZero($notiket+1,6);
			}
			
			$datein = substr(trim($x[$ind][5]),0,4)."-".substr(trim($x[$ind][5]),4,2)."-".substr(trim($x[$ind][5]),6,2)." ".addZero(trim($x[$ind][6]),2).":".addZero(trim($x[$ind][7]),2);
			$dateout = substr(trim($x[$ind][8]),0,4)."-".substr(trim($x[$ind][8]),4,2)."-".substr(trim($x[$ind][8]),6,2)." ".addZero(trim($x[$ind][9]),2).":".addZero(trim($x[$ind][10]),2);
			
			$netto = trim($x[$ind][11]) - trim($x[$ind][12]) - trim($x[$ind][13]);
			$ttlrp = trim($x[$ind][16]) * $netto;
			$rppajak = $ttlrp * (trim($x[$ind][14]) / 100);
			
			if(trim($x[$ind][14])==1)
			{
				$ttlbyr = $ttlrp;
			}
			else
			{
				$ttlbyr = $ttlrp - $rppajak;
			}
      
      $dtNetto=0;
			$dataAp=explode(".",$netto);

      if((intval($dataAp[1])>=1)&&(intval($dataAp[1])<=5)){
        $dtNetto=floor($netto);
      }else{
        $dtNetto=round($netto,0);
      }
      //exit('warning'.$dtNetto);
			$str = "insert into ".$dbname.".pmn_penerimaantbsramp (notiket,kodeorg,unit,koderamp,kodesupplier,nospb,nokendaraan,datein,dateout,beratmasuk,beratkeluar,potongan,netto,jjg,harga,beban_pajak,persenpajak,totalrupiah,rupiahpajak,posted,updateby) values";
			$str.=" ('".$notiket."','".$x[$ind][0]."','".$x[$ind][1]."','".$x[$ind][2]."','".$x[$ind][3]."',";
			$str.="'','".trim($x[$ind][4])."','".$datein."','".$dateout."','".trim($x[$ind][11])."','".trim($x[$ind][12])."','".trim($x[$ind][13])."','".$dtNetto."','0','".trim($x[$ind][16])."','".trim($x[$ind][14])."','".trim($x[$ind][15])."','".$ttlbyr."','".$rppajak."','0','".$_SESSION['standard']['userid']."')";
			try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
				exit(" Gagal :".$e->getMessage()."__".$str);
			}
		  //echo $str."\n";	  
		}

		echo "Notice: Data berhasil diupload";
	break; 
	
	case 'PENERIMAANTBSMANUAL': 
	  $nopoisi=0;
      foreach($x as $key =>$arr){
          if($key==0){
              continue;
          }else{                 
              foreach($arr as $ids =>$rinc){
                     if($nopoisi!=1){
                          $nopoisi=1;
                         
                        if($header[2]=='tanggal')
                        {
                                  
                            if(strlen($x[$key][2])==1)
                            {
                              $x[$key][2]='0'.$x[$key][2];
                            }
                            else if(strlen($x[$key][2])==2)
                            {
                                $x[$key][2]=$x[$key][2];
                            }      
                           
                        }
                          
                     }
                       
              }
         }//else
      }//foreach  
      
      $jmlhRow=count($x);
	  
		$key=1;
		for($ind=1;$ind<$jmlhRow;$ind++){
      if($ind==1){
        $tgl = substr(trim($x[$ind][5]),0,4)."-".substr(trim($x[$ind][5]),4,2)."-".substr(trim($x[$ind][5]),6,2);
        #hapus dulu sebelum insert
        // $sDel="delete from ".$dbname.".pmn_penerimaantbsramp where kodesupplier='".$x[$ind][3]."' and left(datein,10)='".$tgl."'";
        // $owlPDO->exec($sDel);
      }
	  
			##CHECK PENGAKUAN Persediaan
			$temppersediaan = "";
			$optsupplierid = makeOption($dbname,'log_5supplier','kodetimbangan,supplierid');
			$str = "select notransaksi from ".$dbname.".keu_persediaantbs_vw where tanggal = '".tanggalsystem($tanggalmasuk)."' and kodesupplier='".$optsupplierid[$supplierid]."' and jurnal='1'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$temppersediaan = $bar['notransaksi'];
			$optsupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');
			
			if($temppersediaan!='')
			{
				exit("warning : Sudah ada transaksi penerimaan persediaan pada tanggal ".$tanggalmasuk." untuk supplier ".$optsupplier[$optsupplierid[$supplierid]].". Lakukan unposting untuk penerimaan tbs dengan no transaksi : ".$bar['notransaksi']);
			}
			
			##Get No Tiket
			$intltiket = $_POST['intltiket'];
			$str = "select * from ".$dbname.".pabrik_timbangan where notransaksi like '".$intltiket."%' order by notransaksi desc limit 1";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$notiket = $bar['notransaksi'];
			if($notiket=='')
			{
				$notiket = $intltiket."000001";
			}
			else
			{
				$notiket = str_replace($intltiket,'',$notiket);
				$notiket = $intltiket.addZero($notiket+1,6);
			}
			
			$datein = substr(trim($x[$ind][5]),0,4)."-".substr(trim($x[$ind][5]),4,2)."-".substr(trim($x[$ind][5]),6,2)." ".addZero(trim($x[$ind][6]),2).":".addZero(trim($x[$ind][7]),2);
			$dateout = substr(trim($x[$ind][8]),0,4)."-".substr(trim($x[$ind][8]),4,2)."-".substr(trim($x[$ind][8]),6,2)." ".addZero(trim($x[$ind][9]),2).":".addZero(trim($x[$ind][10]),2);
			
			
			$jammasuk = addZero(trim($x[$ind][5]),2).":".addZero(trim($x[$ind][6]),2);
			$jamkeluar = addZero(trim($x[$ind][8]),2).":".addZero(trim($x[$ind][9]),2);
			
			$netto = trim($x[$ind][10]) - trim($x[$ind][11]) - trim($x[$ind][12]);
			      
			$dtNetto=0;
			$dataAp=explode(".",$netto);

			if((intval($dataAp[1])>=1)&&(intval($dataAp[1])<=5)){
				$dtNetto=floor($netto);
			}else{
				$dtNetto=round($netto,0);
			}
	  

			$str = "insert into ".$dbname.".pabrik_timbangan (notransaksi,tanggal,kodecustomer,kodebarang,jammasuk,beratmasuk,jamkeluar,beratkeluar,nokendaraan,username,millcode,kgpotsortasi,beratbersih,ramp,intex) values";
			$str.=" ('".$notiket."','".$x[$ind][4]."','".$x[$ind][2]."','40000003','".$jammasuk."','".trim($x[$ind][10])."','".$jamkeluar."','".trim($x[$ind][11])."','".trim($x[$ind][3])."','".$_SESSION['standard']['userid']."','".trim($x[$ind][1])."','".trim($x[$ind][12])."','".trim($dtNetto)."','','0')";
			
			try
			{
				$owlPDO->exec($str);
			}
			catch (PDOException $e) 
			{
				exit(" Gagal :".$e->getMessage()."__".$str);
			}
		  //echo $str."\n";	  
		}

		echo "Notice: Data berhasil diupload";
	break; 
   
    
	default:
	break;
	}
   
}
?>
