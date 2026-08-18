<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;

switch($proses) {
    case 'add':
                // Validasi, tidak boleh jurnal ke depan
                if(substr(tanggalsystem($data['tanggal']),0,4) > ($_SESSION['org']['period']['tahun'] - 1)) {
                        exit("Warning: ".$_SESSION['lang']['notiftanggaljurnalaudit']);
                }

                #=============== Get Nomor Jurnal
                $whereNo = "kodekelompok='".$data['kodejurnal']."' and kodeorg='".
                        $_SESSION['org']['kodeorganisasi']."'";
                $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
                        $whereNo);
                $noKon = fetchData($query);
                $tmpC = $noKon[0]['nokounter'];
                $tmpC++;
                $counter = addZero($tmpC,3);
                $data['nojurnal'] = tanggalsystem($data['tanggal'])."/".
                        $_SESSION['empl']['lokasitugas']."/".$data['kodejurnal']."/".
                        $counter;
                $nojur = $data['nojurnal'];

                #=============== Insert Process
                # Column
                $column = array('kodejurnal','tanggal','noreferensi','matauang','revisi',
                        'nojurnal','tanggalentry','posting','totaldebet','totalkredit',
                        'amountkoreksi','autojurnal','kurs');

                # Add Default Data
                $data['tanggal'] = tanggalsystem($data['tanggal']);
                $data['tanggalentry'] = date('Ymd');
                $data['posting'] = 0;
                $data['totaldebet'] = 0;
                $data['totalkredit'] = 0;
                $data['amountkoreksi'] = 0;
                $data['autojurnal'] = 0;
                $data['kurs'] = 0;

                # Query
                $query = insertQuery($dbname,'keu_jurnalht',$data,$column);
                try{
                  $test=$owlPDO->exec($query);      
                        if($test){
                             $updData = array('nokounter'=>$tmpC);
                             $query2 = updateQuery($dbname,'keu_5kelompokjurnal',$updData,$whereNo);
                              try{
                                        $test2=$owlPDO->exec($query2);      
                                        if($test2)echo $nojur;
                                }
                                catch (PDOException $e) {
                                           print " Gagal  !: " . $e->getMessage() . "<br/>";
                                           die();
                                    }                       
                        }
                }
                catch (PDOException $e) {
                           print " Gagal  !: " . $e->getMessage() . "<br/>";
                           die();
                }
                break;

    case 'edit':
                $data = $_POST;
                unset($data['nojurnal']);
       
                $query = updateQuery($dbname,'keu_jurnalht',$data,"nojurnal='".$_POST['nojurnal']."'");
                    try{
                              $test=$owlPDO->exec($query);      
                              if($test2){
                               $data['tanggal'] = tanggalnormal($data['tanggal']);
                                echo json_encode($data);
                              }
                      }
                      catch (PDOException $e) {
                                 print " Gagal  !: " . $e->getMessage() . "<br/>";
                                 die();
                          }                   

                $dataz['revisi'] = $_POST['revisi'];
                $query = updateQuery($dbname,'keu_jurnaldt',$dataz,"nojurnal='".$_POST['nojurnal']."'");
                    try{
                              $test=$owlPDO->exec($query);      
                      }
                      catch (PDOException $e) {
                                 print " Gagal  !: " . $e->getMessage() . "<br/>";
                                 die();
                          }
                break;

    case 'delete':
                $query = selectQuery($dbname,'keu_jurnaldt','nojurnal',"nojurnal='".$data['nojurnal']."'");
                $res = fetchData($query);
                if(empty($res)) {
                        $qDel = "delete from `".$dbname."`.`keu_jurnalht` where nojurnal='".$data['nojurnal']."'";
                            try{$test=$owlPDO->exec($qDel);}
                            catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>";die();}
                } else {
                        echo "Warning : ".$_SESSION['lang']['notifdeletejurnal'];
                        exit;
                }
                break;
    default:
        break;
}
?>