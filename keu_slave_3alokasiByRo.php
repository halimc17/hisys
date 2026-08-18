<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/zJournal.php');

$param = $_POST;
$tanggal=str_replace("-","",$param['periode'])."28";//estimasi akhirbulan adalah tanggal 28
#periksa apakah di tujuan ada kebun
$strx="select * from ".$dbname.".organisasi where induk='".$param['pt']."' and tipe='KEBUN'";
$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($resx);
if($numrows<1)
{
    exit(" Error: Tidak ada unit kebun pada PT tujuan");
}
#periksa apakah sudah pernah dilakukan untuk periode dan pt yang sama
$str="select * from  ".$dbname.".keu_jurnaldt_vw where noreferensi='ALK_".$param['kodeorg']."' and tanggal=".$tanggal." 
          and kodeorg in(select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['pt']."' and tipe='KEBUN')
           limit 1";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows>0)
{
    exit(" Error: Sudah pernah dialokasikan untuk PT ".$param['pt']." pada periode ini");
}

$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');
#generate akun sisi  pemilik=============================================
$pemilik['akundebet']=Array();
$pemilik['akunkredit']='';
$str="select noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='RODL'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $pemilik['akunkredit']=$bar->noakunkredit;
}
if($pemilik['akunkredit']=='')
{
    exit(" Error: Parameter jurnal untuk jurnalid RODL belum ada");
}

if( $_SESSION['empl']['kodeorganisasi']!=$param['pt'])//jika tidak dalam satu pt
{
    $kode='inter';
}
 else {
   $kode='intra';    
}
#ambil unit-unit penerima dan luasnya
$penerima['unit']=Array();
while($bar=$resx->fetch())
{
    $penerima['unit'][]=$bar->kodeorganisasi;
    $str1="select akunpiutang from ".$dbname.".keu_5caco where jenis='".$kode."' and kodeorg='".$bar->kodeorganisasi."'";
    $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($bar1=$res1->fetch())
    {
        $pemilik['akundebet'][$bar->kodeorganisasi]=$bar1->akunpiutang;
    }
  #periksa akun debet pemilik untuk masing-masing unit
    foreach($penerima['unit'] as $key=>$val){
        if($pemilik['akundebet'][$val]==''){
            exit(" Error: Akun intra/interco belum ada untuk unit ".$val);
        }
    }
}
#ambil luasan masing-masing unit
$str="select sum(luasareaproduktif)  as luas,left(kodeorg,4) as unit from ".$dbname.".setup_blok group by left(kodeorg,4)";
$luas=Array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $luas[$bar->unit]=$bar->luas;
}
   $totalLuas=0;
    foreach($penerima['unit'] as $key=>$val){
        $luaspenerima[$val]=$luas[$val];
        $totalLuas+=$luas[$val];
    }  
    unset($luas);#destroy sudah tidak dipakai
  if($totalLuas==0){//jika belum memiliki lahan maka dibagi rata
      $jumlahunit=count($penerima['unit']);
        foreach($penerima['unit'] as $key=>$val){
           $jumlah[$val]=$param['jumah']/$jumlahunit;
       }     
  }
  else{#jika tidak maka bagi per porsi luasan
        foreach($penerima['unit'] as $key=>$val){
           $jumlah[$val]= ($luaspenerima[$val]/$totalLuas)*$param['jumlah'];
       }       
  }
  $arrNoJurnal=Array();
#===================================================================
  #generate jurnal sisi pemilik
  # Get Journal Counter
$kodejurnal='ROT';
$str="select max(nojurnal) as nojurnal from ".$dbname.".keu_jurnalht where nojurnal like '%".$tanggal."/".$param['kodeorg']."/".$kodejurnal."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
     $tmpKonter[0]['nokounter']=substr($bar->nojurnal,-3,3);
 }
$konter = addZero($tmpKonter[0]['nokounter']+1,3);

# Prep No Jurnal
$nojurnal = $tanggal."/".$param['kodeorg']."/".$kodejurnal."/".$konter;
$arrNoJurnal[]=$nojurnal;

  $data['header'][] = array(
    'nojurnal'=>$nojurnal,
    'kodejurnal'=>$kodejurnal,
    'tanggal'=>$tanggal,
    'tanggalentry'=>date('Ymd'),
    'posting'=>'1',
    'totaldebet'=>$param['jumlah'],
    'totalkredit'=>-1*$param['jumlah'],
    'amountkoreksi'=>'0',
    'noreferensi'=>'ALK_'.$param['kodeorg'],
    'autojurnal'=>'1',
    'matauang'=>'IDR',
    'kurs'=>'1',
    'revisi'=>'0'    
);
  
$noUrut=1;

  $dataRes['detail'][] = array(
        'nojurnal'=>$nojurnal,
        'tanggal'=>$tanggal,
        'nourut'=>$noUrut,
        'noakun'=>$pemilik['akunkredit'],
        'keterangan'=>'Biaya Tidak Langsung yang dialokasi',
        'jumlah'=>-1*$param['jumlah'],
        'matauang'=>'IDR',
        'kurs'=>'1',
        'kodeorg'=>$param['kodeorg'],
        'kodekegiatan'=>'',
        'kodeasset'=>'',
        'kodebarang'=>'',
        'nik'=>'0',
        'kodecustomer'=>'',
        'kodesupplier'=>'',
        'noreferensi'=>'ALK_'.$param['kodeorg'],
        'noaruskas'=>'',
        'kodevhc'=>'',
        'nodok'=>'',
        'kodeblok'=>'',
        'revisi'=>'0',
        'kodesegment' => $defSegment      
    );
    $noUrut++;
#debet sisi pemilik
         foreach($penerima['unit'] as $key=>$val){
                $dataRes['detail'][] = array(
                      'nojurnal'=>$nojurnal,
                      'tanggal'=>$tanggal,
                      'nourut'=>$noUrut,
                      'noakun'=>$pemilik['akundebet'][$val],
                      'keterangan'=>'Alokasi Biaya Tidak Langsung RO/HO',
                      'jumlah'=>$jumlah[$val],
                      'matauang'=>'IDR',
                      'kurs'=>'1',
                      'kodeorg'=>$param['kodeorg'],
                      'kodekegiatan'=>'',
                      'kodeasset'=>'',
                      'kodebarang'=>'',
                      'nik'=>'0',
                      'kodecustomer'=>'',
                      'kodesupplier'=>'',
                      'noreferensi'=>'ALK_'.$param['kodeorg'],
                      'noaruskas'=>'',
                      'kodevhc'=>'',
                      'nodok'=>'',
                      'kodeblok'=>'',
                      'revisi'=>'0',
                      'kodesegment' => $defSegment
                  );
                  $noUrut++;           
       }   
  
#======================================Create jurnal sisi unit
#1 periksa proporsi TM dan TBM masing-masing unit
    $luastbm=Array(); 
    $jatahtm=Array();
    $jatahtbm=Array();
    foreach($penerima['unit'] as $key=>$val){
         if($luaspenerima[$val]>0){
             $str="select luasareaproduktif as luastbm,kodeorg from ".$dbname.".setup_blok where left(kodeorg,4)='".$val."' 
                       and statusblok in('LC','TB','TBM','TBM1','TBM2','TBM3','TBMPRO','BBT')";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch())
                {
                 $luastbm[$val]+=$bar->luastbm;
                 $blok[$val][]=$bar->kodeorg;
             }
             
           $jatahtbm[$val]=($luastbm[$val]/$luaspenerima[$val])*$jumlah[$val];               
           $jatahtm[$val]=$jumlah[$val]- $jatahtbm[$val];   
         }
         else
         {
                $jatahtm[$val]=$jumlah[$val];
                $jatahtbm[$val]=0;             
         }
    } 
#generate jurnal sisi penerima=========================================================
$penerima['akunkredit']='';  
    $str1="select akunhutang from ".$dbname.".keu_5caco where jenis='".$kode."' and kodeorg='".$param['kodeorg']."'";
    $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while($bar1=$res1->fetch())
    {
       $penerima['akunkredit']=$bar1->akunhutang;
    }
 if( $penerima['akunkredit']=='')
 {
            exit(" Error: Akun intra/interco belum ada untuk unit ".$param['kodeorg']);
 }
$penerima['akundebet']['tm']='';
$penerima['akundebet']['tbm']='';
$str="select noakundebet,jurnalid from ".$dbname.".keu_5parameterjurnal where jurnalid in('ROTBM','ROTM')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if($bar->jurnalid=='ROTM')
            $penerima['akundebet']['tm']=$bar->noakundebet;
    else
            $penerima['akundebet']['tbm']=$bar->noakundebet;
} 
if($penerima['akundebet']['tm']=='' or $penerima['akundebet']['tbm']==''){
    exit(" Error: No.Akun debet untuk ROTBM atau ROTM belum terisi pada parameterjurnal");
}
#generate jurnal========================================================
        $queryJ = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
            "kodeorg='".$param['pt']."' and kodekelompok='".$kodejurnal."'");
        $tmpKonter = fetchData($queryJ);
        $konter = addZero($tmpKonter[0]['nokounter']+1,3);
foreach($penerima['unit'] as $key=>$val){
        # Prep No Jurnal
    $konter = addZero($konter+1,3);  
        $nojurnal= $tanggal."/".$val."/".$kodejurnal."/".$konter;
       $arrNoJurnal[]=$nojurnal;
//        echo $konter."<>";
            $data['header'][] = array(
              'nojurnal'=>$nojurnal,
              'kodejurnal'=>$kodejurnal,
              'tanggal'=>$tanggal,
              'tanggalentry'=>date('Ymd'),
              'posting'=>'1',
              'totaldebet'=>$jumlah[$val],
              'totalkredit'=>-1*$jumlah[$val],
              'amountkoreksi'=>'0',
              'noreferensi'=>'ALK_'.$param['kodeorg'],
              'autojurnal'=>'1',
              'matauang'=>'IDR',
              'kurs'=>'1',
              'revisi'=>'0',              
          ); 
  
    $noUrut=1;
    #kredit
      $dataRes['detail'][] = array(
            'nojurnal'=>$nojurnal,
            'tanggal'=>$tanggal,
            'nourut'=>$noUrut,
            'noakun'=>$penerima['akunkredit'],
            'keterangan'=>'Alokasi Biaya Tidak Langsung RO/HO',
            'jumlah'=>-1*$jumlah[$val],
            'matauang'=>'IDR',
            'kurs'=>'1',
            'kodeorg'=>$val,
            'kodekegiatan'=>'',
            'kodeasset'=>'',
            'kodebarang'=>'',
            'nik'=>'0',
            'kodecustomer'=>'',
            'kodesupplier'=>'',
            'noreferensi'=>'ALK_'.$param['kodeorg'],
            'noaruskas'=>'',
            'kodevhc'=>'',
            'nodok'=>'',
            'kodeblok'=>'',
            'revisi'=>'0',
               'kodesegment' => $defSegment           
        );
        $noUrut++;
      #debet TM               
         if($jatahtm[$val]>0){
                   $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>$tanggal,
                    'nourut'=>$noUrut,
                    'noakun'=>$penerima['akundebet']['tm'],
                    'keterangan'=>'Alokasi Biaya Tidak Langsung RO/HO',
                    'jumlah'=>$jatahtm[$val],
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'kodeorg'=>$val,
                    'kodekegiatan'=>'',
                    'kodeasset'=>'',
                    'kodebarang'=>'',
                    'nik'=>'0',
                    'kodecustomer'=>'',
                    'kodesupplier'=>'',
                    'noreferensi'=>'ALK_'.$param['kodeorg'],
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>'',
                    'kodeblok'=>'',
                    'revisi'=>'0',
               'kodesegment' => $defSegment                        
                );
                $noUrut++;
         } 
        #debet TBM 
       if(count($blok[$val])>0){  
           foreach($blok[$val] as $kunci=>$kodeblok){
                   $dataRes['detail'][] = array(
                    'nojurnal'=>$nojurnal,
                    'tanggal'=>$tanggal,
                    'nourut'=>$noUrut,
                    'noakun'=>$penerima['akundebet']['tbm'],
                    'keterangan'=>'Alokasi Biaya Tidak Langsung RO/HO',
                    'jumlah'=>$jatahtbm[$val]/count($blok[$val]),
                    'matauang'=>'IDR',
                    'kurs'=>'1',
                    'kodeorg'=>$val,
                    'kodekegiatan'=>'',
                    'kodeasset'=>'',
                    'kodebarang'=>'',
                    'nik'=>'0',
                    'kodecustomer'=>'',
                    'kodesupplier'=>'',
                    'noreferensi'=>'ALK_'.$param['kodeorg'],
                    'noaruskas'=>'',
                    'kodevhc'=>'',
                    'nodok'=>'',
                    'kodeblok'=>$kodeblok,
                    'revisi'=>'0' ,
               'kodesegment' => $defSegment                        
                );
                $noUrut++;            
        } 
     }   

}       
#=== Insert Data ===================================================================
$errorDB = "";
# Header
foreach($data['header'] as $key=>$dataDet) {
    $queryH = insertQuery($dbname,'keu_jurnalht',$dataDet);
    try{$owlPDO->exec($queryH); }
    catch (PDOException $e) {
        $errorDB .= "Header : " . $e->getMessage() . "\n";
    }
}
# Detail
if($errorDB=='') {
    foreach($dataRes['detail'] as $key=>$dataDet) {
        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
            try{$owlPDO->exec($queryD); }
            catch (PDOException $e) {
                $errorDB .= "Detail ".$key." :" . $e->getMessage() . "\n";
            }        
    }
   #update jurnal counter
        $queryKonter = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter+1),
            "kodeorg='".$param['pt']."' and kodekelompok='".$kodejurnal."'");
            try{$owlPDO->exec($queryKonter); }
            catch (PDOException $e) {
                 $errorDB .= "Update Counter Error :" . $e->getMessage() . "\n";
            }      
}
if($errorDB!="") {# Rollback
  foreach($arrNoJurnal as $key =>$nojur) { 
        $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where nojurnal='".$nojur."'";
            try{$owlPDO->exec($queryKonter); }
            catch (PDOException $e) {
                 $errorDB .= "Rollback 1 Error :" . $e->getMessage() . "\n";
            }     
    }
  echo "Error ".$errorDB;  
}
?>