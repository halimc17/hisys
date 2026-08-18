<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');

$supplierid = checkPostGet('supplierid','');
$npwp = checkPostGet('npwp','');
$namanpwp = checkPostGet('namanpwp','');
$jalan = checkPostGet('jalan','');
$blok = checkPostGet('blok','');
$nomor = checkPostGet('nomor','');
$rt = checkPostGet('rt','');
$rw = checkPostGet('rw','');
$kecamatan = checkPostGet('kecamatan','');
$kelurahan = checkPostGet('kelurahan','');
$kabupaten = checkPostGet('kabupaten','');
$propinsi = checkPostGet('propinsi','');
$kodepos = checkPostGet('kodepos','');
$telp_no = checkPostGet('telp_no','');
$aktif = checkPostGet('aktif','');
$method = checkPostGet('method','');
$id = checkPostGet('id','');
$namafile = checkPostGet('namafile','');

$txtsearch = checkPostGet('txtsearch','');
$txtNoakun = checkPostGet('txtNoakun','');
$caristatusup = checkPostGet('caristatusup','');
$caribadan = checkPostGet('caribadan','');


$id_supplier = checkPostGet('id_supplier','');
$bank = checkPostGet('bank','');
$rekening = checkPostGet('rekening','');
$atasnama = checkPostGet('atasnama','');
$cabang = checkPostGet('cabang','');
$kota = checkPostGet('kota','');
$negara = checkPostGet('negara','');
$matauang = checkPostGet('matauang','');
$def = checkPostGet('def','');
$statusbank = checkPostGet('statusbank','');


$strnama = array ("0"=>"tidak aktif","1"=>"aktif");
$strnamaper = array ("0"=>"Proses persetujuan","1"=>"Disetujui","2"=>"Ditolak");
$jnsapp = "DS";


switch ($method) {
  case'loadData':
    $tab = "<thead>
              <tr class=header>
                <th align=center>".$_SESSION['lang']['nourut']."</th>
                <th align=center>".$_SESSION['lang']['kodesupplier']."</th>
                <th align=center>".$_SESSION['lang']['namasupplier']."</th>
                <th align=center>".$_SESSION['lang']['badanusaha']."</th>
                <th align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['pemilik']."</th>
                <th align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['direktur']."</th>
                <th align=center>".$_SESSION['lang']['namapj']."</th>
                <th align=center>".$_SESSION['lang']['jabatan']."</th>
                <th align=center>".$_SESSION['lang']['status'] . " " . $_SESSION['lang']['supplier']."</th>
                <th align='center'>".$_SESSION['lang']['action']."</th>
              </tr>
            </thead> 
            <tbody>"; 

    $where = "";
    if($txtsearch != ''){
      $where .= " and namasupplier LIKE '%".$txtsearch."%'";
    }
    if($txtNoakun!=''){
      $where .= " and supplierid LIKE '%".$txtNoakun."%'";
    }
    if($caristatusup!=''){
      $where .= " and status LIKE '%".$caristatusup."%'";
    }
    if($caribadan!=''){
      $where .= " and badanusaha LIKE '%".$caribadan."%'";
    }
    
    $limit = 20;
    $page = 1;
    $p = new Paging; 
      
    if (isset($_POST['page'])) {
      $page = $_POST['page'];
      if ($page < 1)
        $page = 1;
    }
      
    $posisi = $p->cariPosisi($limit,$page);
    $ql2 = "select supplierid from " . $dbname . ".log_5supplier where 1=1 ".$where." order by namasupplier asc";
    $rjml = fetchData($ql2);
    $jlhbrs = count($rjml);
    $jml = $p->jumlahHalaman($jlhbrs,$limit);
    $nor = 0;
      
    $input = "select a.*,b.email,b.full_name from " . $dbname . ".log_5supplier a 
              left join log_5supuser b on a.supplierid = b.id_supplier
              where 1=1 ".$where." order by namasupplier asc LIMIT $posisi,$limit";
    $n = $owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
    $no = $posisi+1;
    while ($d = $n->fetch()) {
      $whereJam = "supplierid='".$d['supplierid']."'";
      $nmSup = makeOption($dbname, 'log_5supkelompok', 'tipe,tipe',$whereJam);
      $sup = "";
      foreach($nmSup as $k =>$v){
        $sup .= $v.",";
      }
      $sup = substr($sup, 0, -1);

      $tab .= "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=left>" . $d['supplierid'] . "</td>
                <td align=left>" . $d['namasupplier'] . "</td>
                <td align=left>" . $d['badanusaha'] . "</td>
                <td align=left>" . $d['namapemilik'] . "</td>
                <td align=left>" . $d['namadirektur'] . "</td>
                <td align=left>" . $d['namapenanggungjawab'] . "</td>
                <td align=left>" . $d['jabatan'] . "</td>
                <td align=center>" . $strnama[$d['status']]."</td>
                <td align=center>
                  <img src=images/addplus.png class=zImgBtn title='Add Detail Supplier' onclick=\"detaildt('" . $d['supplierid'] . "','" .$d['namasupplier']."');\">
                </td>
              </tr>"; 
      $no++;
    }

    $buttonaction = array(
      'first' =>  'onclick="loadData(1);"',
      'prev'  =>  'onclick="loadData('.($page-1).');"',
      'next'  =>  'onclick="loadData('.($page+1).');"',
      'last'  =>  'onclick="loadData('.($jml).')"',
      'pages' =>  'id="pages" name="pages" onchange="loadData(this.value);"'
    );
    
    $tab .= "</tbody>
            <tfoot>
              <tr>
                <td colspan=15 align=center>
                  ".$p->navHalaman($page,$jml,$buttonaction)."
                </td>
              </tr>
            </tfoot>
            </table>";

    echo $tab;
  break;

  case'detaildt':
    $nmsup =  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

    $sql = "SELECT * FROM ".$dbname.".keu_5daftarbank where status='1'";
    $res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while ($data = $res->fetch()) {
      $optbank .= "<option value=".$data['kodebank'].">".$data['namabank']."</option>";
    }

    $str1 = $owlPDO->query("select kode,matauang from ".$dbname.".setup_matauang 
          order by matauang");
    $str1->setFetchMode(PDO::FETCH_OBJ);
    $optCurr = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
    while($bar = $str1->fetch()){
        $optCurr .= "<option value='".$bar->kode."'>".$bar->matauang."</option>";
    }

    $tab = "<fieldset>
              <input id='methodAkun' class='myinputtext' name='prosses' type='hidden' value='insert'>
              <input id='idsupplier' class='myinputtext' name='idsupplier' type='hidden' value='".$supplierid."'>
              <table>
                <tr>
                    <td>".$_SESSION['lang']['supplier']."</td>
                    <td>:</td>
                    <td><input type=text id=nmsupp nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" value='".$nmsup[$supplierid]."' disabled></td>
                
                    <td>".$_SESSION['lang']['namabank']."</td> 
                    <td>:</td>
                    <td><select id=bank style=\"width:205px;\" >".$optbank."</select></td>
                </tr>

                <tr>
                  <td>".$_SESSION['lang']['norek']."</td> 
                  <td>:</td>
                  <td><input type=text id=rekening nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
            
                  <td>".$_SESSION['lang']['atasnama']."</td> 
                  <td>:</td>
                  <td><input type=text  id=atasnama nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>

                <tr>
                  <td>".$_SESSION['lang']['cabang']."</td> 
                  <td>:</td>
                  <td><input type=text  id=cabang nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
            
                  <td>".$_SESSION['lang']['kota']."</td> 
                  <td>:</td>
                  <td><input type=text  id=kota nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
                </tr>

                <tr>
                  <td>".$_SESSION['lang']['negara']."</td> 
                  <td>:</td>
                  <td><input type=text  id=negara nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\"></td>
             
                  <td>".$_SESSION['lang']['matauang']."</td>
                  <td>:</td>
                  <td><select id=matauang style=\"width:205px;\">".$optCurr."</select></td>
                </tr>
                
                <tr>
                  <td>".$_SESSION['lang']['default']."</td>
                  <td>:</td>
                  <td><input type=checkbox id=def>".$_SESSION['lang']['yes']."/".$_SESSION['lang']['no']."</td>
           
                  <td>".$_SESSION['lang']['status']."</td>
                  <td>:</td>
                  <td><input type=checkbox id=statusbank>".$_SESSION['lang']['aktif']."/".$_SESSION['lang']['tidakaktif']."</td>
                </tr>

                <tr>
                  <td colspan=2></td>
                  <td>
                    <button class=mybutton onclick=saveAkun()>Simpan</button>
                    <button class=mybutton onclick=cancelAkun()>Reset</button>
                  </td>
                </tr>
              </table>
            </form> 
          </fieldset>";

    $tab .= "<table class=sortable cellpadding=1 cellspacing=1 border=0 style='width:100%;margin-top:10px'>
              <thead>
                <tr class=rowheader>
                 <td align=center>" . $_SESSION['lang']['nourut'] . "</td>
                 <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
                 <td align=center>" . $_SESSION['lang']['namabank'] . "</td>
                 <td align=center>" . $_SESSION['lang']['norek'] . "</td>
                 <td align=center>" . $_SESSION['lang']['atasnama'] . "</td>
                 <td align=center>" . $_SESSION['lang']['cabang'] . "</td>
                 <td align=center>" . $_SESSION['lang']['kota'] . "</td>
                 <td align=center>" . $_SESSION['lang']['negara'] . "</td>
                 <td align=center>" . $_SESSION['lang']['matauang'] . "</td>
                 <td align=center>" . $_SESSION['lang']['default'] . "</td>
                 <td align=center>" . $_SESSION['lang']['status'] . "</td>
                 <td align=center>" . $_SESSION['lang']['action'] . "</td>
               </tr>
              </thead>
              <tbody>";

    $nmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
    $nmbank = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
    $input = "select * from " . $dbname . ".log_5rekbank where supplierid = '".$supplierid."'";
    $n=$owlPDO->query($input) or die(print " Gagal: ".PDOException::getMessage());
    $n->setFetchMode(PDO::FETCH_ASSOC);
    while ($d = $n->fetch()) {
      @$no+=1;
      $tab .= "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td align=left>" . (isset($nmSup[$d['supplierid']]) ? $nmSup[$d['supplierid']] : '') . "</td>
                <td align=left>" . $nmbank[$d['idbank']] . "</td>
                <td align=left>" . $d['rekening'] . "</td>
                <td align=left>" . $d['an'] . "</td>
                <td align=left>" . $d['cabang'] . "</td>
                <td align=left>" . $d['kota'] . "</td>
                <td align=left>" . $d['negara'] . "</td>
                <td align=left>" . $d['matauang'] . "</td>
                <td align=left>" . $strnama[$d['def']]."</td>
                <td align=left>" . $strnama[$d['isactive']]."</td>
                <td align=center>
                  <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"editAkun('" . $d['supplierid'] . "',". "'" . $d['idbank'] . "',". "'" . $d['rekening'] . "',". "'" . $d['an'] . "',". "'" . $d['cabang'] . "',". "'" . $d['kota'] . "',". "'" . $d['negara'] . "',". "'" . $d['matauang'] . "','" . $d['def'] . "','" . $d['isactive'] . "');\">
                </td>
            </tr>";
    }

    $tab .= "</tbody></table>";

    echo $tab;
  break;
  
  case'insert':
		$str="select * from ".$dbname.".keu_5daftarbank where kodebank='".$bank."' ";
		$res=fetchData($str);
		foreach($res as $bar){
			$inisial=$bar['inisial'];
		}
  
		$str="select * from ".$dbname.".log_5rekbank where rekening='".$rekening."' and supplierid='".$id_supplier."'";
        $qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $numRows=owlBaris($qry);
        if($numRows>=1){
          echo "Error: Nomor rekening Sudah Ada.";
        }
        else{
          $input="insert into " . $dbname . ".log_5rekbank (supplierid,idbank,rekening,an,cabang,kota,negara,matauang,updateby,def,isactive,statusyangdiinginkan,statuspersetujuan,bank)
                values ('" . $id_supplier . "','" . $bank . "','" . $rekening . "','" . $atasnama . "','" . $cabang . "','" . $kota . "','" . $negara . "','" . $matauang . "','" . $_SESSION['standard']['userid'] . "','" . $def . "','" . $statusbank . "','" . $statusbank . "','1','".$inisial." - ".$cabang."')";
				
          try {
				$owlPDO->exec($input); 
          }  catch (PDOException $e){
			  echo " Gagal," . addslashes($e->getMessage());
			  die();
          }
        }
  break;
  
  case 'update':
          $strx = selectQuery($dbname,"log_5rekbank","*","supplierid='".$id_supplier."'  and idbank='".$bank."' and matauang='".$matauang."'");
          $resx = fetchData($strx);
          $oldx['supplierid'] = $resx[0]['supplierid'];
          $oldx['idbank'] = $resx[0]['idbank'];
          $oldx['bank'] = $resx[0]['bank'];
          $oldx['rekening'] = $resx[0]['rekening'];
          $oldx['an'] = $resx[0]['an'];
          $oldx['cabang'] = $resx[0]['cabang'];
          $oldx['kota'] = $resx[0]['kota'];
          $oldx['negara'] = $resx[0]['negara'];
          $oldx['matauang'] = $resx[0]['matauang'];
          $oldx['def'] = $resx[0]['def'];
          $oldx['isactive'] = $resx[0]['isactive'];
          $perubahanx = $resx[0]['perubahan'];

           $textubah=$oldx['supplierid']."##".$oldx['idbank']."##".$oldx['bank']."##".$oldx['rekening']."##".$oldx['an']."##".$oldx['cabang']."##".$oldx['kota']."##".$oldx['negara']."##".$oldx['matauang']."##".$oldx['def']."##".$oldx['isactive'];
        #defaultnya
        $sDefault="select def,idbank,matauang from ".$dbname.".log_5rekbank where supplierid='".$id_supplier."' and def=1";
        $rDefault=fetchData($sDefault);

            $input="update ".$dbname.".log_5rekbank set def='".$def."',
               an='".$atasnama."', cabang='".$cabang."', kota='".$kota."',
               negara='".$negara."', matauang='".$matauang."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Ymdhis')."',
               isactive='".$statusbank."',statusyangdiinginkan='".$statusbank."',statuspersetujuan='1',perubahan='".$textubah."' 
              where supplierid='".$id_supplier."' and idbank='".$bank."' and matauang='".$matauang."' and rekening='".$rekening."'";

            try{$owlPDO->exec($input); }
            catch (PDOException $e){ echo " Gagal," . addslashes($e->getMessage());die();}


        break;



    default:
    break;
}
?>
