<?php
include_once("lib/zLib.php");

/**
 * zJournal
 * Class untuk Jurnal
 */
class zJournal {
        /**
         * getCounter
         * Get Counter Jurnal dari Kode Jurnal tertentu
         */
        public function getCounter($pt,$kodeJurnal) {
                global $dbname;

                $qKelompok = selectQuery($dbname,'keu_5kelompokjurnal',"nokounter",
                                                                 "kodeorg='".$pt."' and kodekelompok='".$kodeJurnal."'");
                $resKelompok = fetchData($qKelompok);
                if(empty($resKelompok)) {
                        return 0;
                } else {
                        return $resKelompok[0]['nokounter'];
                }
        }

        /**
         * genNoJournal
         * Generate Nomor Jurnal
         */
        public function genNoJournal($tanggal,$kodeUnit,$kodeJurnal,$counter) {
                if(substr($tanggal,2,1)=='-') {
                        $tanggal = tanggalsystem($tanggal);
                } elseif(substr($tanggal,4,1)=='-') {
                        $tanggal = str_replace('-','',$tanggal);
                }

                $nojurnal = $tanggal.'/'.$kodeUnit.'/'.$kodeJurnal.'/'.
                        str_pad($counter+1,3,'0',STR_PAD_LEFT);
                return $nojurnal;
        }

        /**
         * getParam
         * Get Parameter Jurnal based on kodeaplikasi dan kodejurnal
         */
        public function getParam($holding, $kodeApp, $kodeJurnal) {
                global $dbname;

                $qParam = selectQuery($dbname,'keu_5parameterjurnal',
                                                          'noakundebet,noakunkredit,sampaidebet,sampaikredit',
                                                          "kodeorg='".$holding."' and kodeaplikasi='".$kodeApp.
                                                          "' and jurnalid='".$kodeJurnal."' and aktif=1");
                $resParam = fetchData($qParam);
                if(empty($resParam)) {
                        return array();
                } else {
                        return $resParam[0];
                }
        }

        /**
         * doJournal
         * Lakukan Jurnal berdasarkan data yang diberikan
         */
        public function doJournal($pt,$kodeJurnal,$dataRes,$counter,$ketKelompok="",
                                                          $updateCounter = true) {
                global $dbname;
                global $owlPDO;
                #Cek Nojurnal sudah terdaftar, Jika sudah terdaftar exit
                $sCek=$owlPDO->query("select nojurnal from ".$dbname.".keu_jurnaldt where nojurnal='".$dataRes['header']['nojurnal']."'");
                $sCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=owlBaris($sCek);
                if($rCek!=0){
                        exit("warning: Nojurnal :".$dataRes['header']['nojurnal']." sudah ada");
                }
                $qHeader = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                $qDetail = insertQuery($dbname,'keu_jurnaldt',$dataRes['detail']);

                $dataIns = array($pt,$kodeJurnal,$ketKelompok,$counter);
                $insCounter = insertQuery($dbname,'keu_5kelompokjurnal',$dataIns);
                $updCounter = updateQuery($dbname,'keu_5kelompokjurnal',
                                                                  array('nokounter'=>$counter),
                                                                  "kodeorg='".$pt."' and kodekelompok='".$kodeJurnal."'");

                $test=false;
                try{$test=$owlPDO->exec($qHeader);}
                  catch (PDOException $e) {
                        print " Header DB Error  !: " . $e->getMessage() . "<br/>";
                        $this->rbJournal($dataRes['header']['nojurnal']);exit;
                  }
                  if($test){
                             try{$test=$owlPDO->exec($qDetail);}
                            catch (PDOException $e) {
                                    print "Detail DB Error  !: " . $e->getMessage() . "<br/>";
                                    $this->rbJournal($dataRes['header']['nojurnal']);
                            }
                            if($test){
                                        try{$owlPDO->exec($insCounter);}
                                        catch (PDOException $e) {
                                            //do nothing
                                        }
                                        try{$owlPDO->exec($updCounter);}
                                        catch (PDOException $e) {
                                            print "Counter update Error  !: " . $e->getMessage() . "<br/>";
                                            $this->rbJournal($dataRes['header']['nojurnal']);exit;
                                        }
                      }
                 }
}                 

        /**
         * rbJournal
         * Rollback Journal / Delete Journal
         */
        public function rbJournal($nojurnal) {
                global $dbname;

                $qDel = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
                //$qDel2 = deleteQuery($dbname,'keu_jurnaldt',"nojurnal='".$nojurnal."'");
                try{$owlPDO->exec($qDel);}
                catch (PDOException $e) {
                    print "Rollback Journal Error  !: " . $e->getMessage() . "<br/>";
                 }
        }
}