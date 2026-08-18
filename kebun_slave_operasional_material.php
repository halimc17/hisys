<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses = $_GET['proses'];
$param = $_POST;
switch($proses) {
    case 'add':
                cekVolume();
                cekSaldo(); // Cek Saldo

                # Kegiatan harus ada
                $qKeg = selectQuery($dbname,'kebun_prestasi','*',"notransaksi='".$param['notransaksi']."'");
                $resKeg = fetchData($qKeg);
                if(empty($resKeg)) {
                        echo 'Warning : Kegiatan harus diisi lebih dahulu';
                        exit;
                }

                # Set Kolom dan Extract Data
                $cols = array(
                        'kodeorg','kwantitasha','kodegudang','kodebarang','kwantitas',
                        'notransaksi','hargasatuan'
                );
                $data = $param;  
                unset($data['numRow']);
                $data['hargasatuan'] = 0;

                # Barang harus ada
                if($data['kodebarang']=='' or $data['kodebarang']=='0') {
                        echo 'Warning : Barang harus diisi';
                        exit;
                }

                # Cek Ha
                $tipe='BLOK';//default
                $str="select tipe from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
                        $tipe=$bar->tipe;
                }
                if($tipe!='BLOK')
                {}
                else
                {
                        $theHa = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$param['kodeorg']."'");
                        if(strlen(trim($data['kodeorg']))==6)
                        {

                        }
                        else if($data['kwantitasha']>$theHa[$data['kodeorg']]) {
                                echo "Validation Error : Ha harus lebih kecil dari Luas produktif Blok:".$data['kodeorg'];
                                exit;
                        }
                }

                //Cek Apabila Pilihan Gudang berbeda dengan Afdeling
       //          $optCheckTipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
       //          if($optCheckTipe[$param['kodeorg']] == 'BIBITAN'){
       //                  if(substr($param['kodeorg'],0,4) != substr($param['kodegudang'],0,4)){
       //                          echo "Validation Error : Kesalahan pada pilihan gudang.";
       //                          exit;
       //                  }
       //          }else{
       //                  if(substr($param['kodeorg'],0,4) != substr($param['kodegudang'],0,4)){
							// //$otpTipeBlok = makeOption($dbname,'setup_blok','kodeorg,intiplasma',"kodeorg = '".$param['kodeorg']."'");
							// //if($otpTipeBlok[$param['kodeorg']]=='P')
							// //{
								
							// //}
							// //else
							// //{
							// 	echo "Validation Error : Kesalahan pada pilihan gudang.";
       //                          exit;
							// //}
       //                  }
       //          }
                //Cek Apabila Pilihan Gudang berbeda dengan Afdeling
                $scek="select * from ".$dbname.".kebun_5gudangtransaksi where afdeling='".substr($param['kodeorg'], 0,6)."' and status=1";
                $rcek=fetchdata($scek);
                if($rcek[0]['kodegudang']!=$param['kodegudang']){
                    echo "Validation Error : Kesalahan pada pilihan gudang.";
                    exit;
                }
                # Insert
                $query = insertQuery($dbname,'kebun_pakaimaterial',$data,$cols);
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }

                unset($data['notransaksi']);
                unset($data['hargasatuan']);
                $res = "";
                foreach($data as $cont) {
                        $res .= "##".$cont;
                }

                $result = "{res:\"".$res."\",theme:\"".$_SESSION['theme']."\"}";
                echo $result;
                break;
    case 'edit':
                cekVolume();
                cekSaldo(); // Cek Saldo

                $data = $param;
                unset($data['notransaksi']);
                foreach($data as $key=>$cont) {
                        if(substr($key,0,5)=='cond_') {
                        unset($data[$key]);
                        }
                }

                # Cek Ha
                $theHa = makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',
                        "kodeorg='".$param['kodeorg']."'");
                if($data['kwantitasha']>$theHa[$data['kodeorg']]) {
                        echo "Validation Error : Ha harus lebih kecil dari Luas produktif Blok";
                        exit;
                }

                //Cek Apabila Pilihan Gudang berbeda dengan Afdeling
                // $optCheckTipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
                // if($optCheckTipe[$param['kodeorg']] == 'BIBITAN'){
                //         if(substr($param['kodeorg'],0,4) != substr($param['kodegudang'],0,4) || substr($param['kodeorg'],4,1) != substr($param['kodegudang'],4,1)){
                //                 echo "Validation Error : Kesalahan pada pilihan gudang.";
                //                 exit;
                //         }
                // }else{
                //         if(substr($param['kodeorg'],0,4) != substr($param['kodegudang'],0,4) || substr($param['kodeorg'],5,1) != substr($param['kodegudang'],5,1)){
                //             echo "Validation Error : Kesalahan pada pilihan gudang.";
                //                 exit;            
                //         }
                // }
                //Cek Apabila Pilihan Gudang berbeda dengan Afdeling
                $scek="select * from ".$dbname.".kebun_5gudangtransaksi where afdeling='".substr($param['kodeorg'], 0,6)."' and status=1";
                $rcek=fetchdata($scek);
                if($rcek[0]['kodegudang']!=$param['kodegudang']){
                    echo "Validation Error : Kesalahan pada pilihan gudang.";
                    exit;
                }
                $where = "notransaksi='".$param['notransaksi']."' and kodeorg='".
                        $param['cond_kodeorg']."' and kodebarang='".$param['cond_kodebarang']."'";
                $query = updateQuery($dbname,'kebun_pakaimaterial',$data,$where);
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                echo json_encode($param);
                break;
    case 'delete':
                $where = "notransaksi='".$param['notransaksi']."' and kodeorg='".
                        $param['kodeorg']."' and kodebarang='".$param['kodebarang']."'";
                $query = "delete from `".$dbname."`.`kebun_pakaimaterial` where ".$where;
                try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
                break;
    default:
    break;
}

function cekVolume() {
        global $dbname;
        global $param;

        // Get Prestasi
        $qPres = selectQuery($dbname,'kebun_prestasi',"hasilkerja","notransaksi='".$param['notransaksi']."'");
        $resPres = fetchData($qPres);
        if(empty($resPres)) exit("Warning: Prestasi belum ada");

        if($param['kwantitasha']>$resPres[0]['hasilkerja'])
                exit("Warning: Volume tidak boleh lebih besar dari Hasil Kerja Prestasi\n".
                         "Volume: ".$param['kwantitasha']."\nHasil Kerja: ".$resPres[0]['hasilkerja']);
}

function cekSaldo() {
        global $dbname;
        global $param;

        $where = "a.kodebarang = '".$param['kodebarang']."' and a.inactive=0 and ".
                "b.kodegudang = '".$param['kodegudang']."'";
        $query = "SELECT a.kodebarang,a.namabarang,a.satuan,b.saldoqty as saldo ";
        $query .= "FROM ".$dbname.".`log_5masterbarang` a ";
        $query .= "LEFT JOIN (".$dbname.".log_5masterbarangdt b) ";
        $query .= "ON a.kodebarang=b.kodebarang ";
        $query .= "WHERE ".$where;

        $res = fetchData($query);

        if(empty($res)) {
                exit("Warning: Barang tidak memiliki stok");
        } else {
                if($param['kwantitas'] > $res[0]['saldo']) {
                        exit("Warning: Kuantitas melebihi stok\nStok: ".$res[0]['saldo']);
                }
        }
}