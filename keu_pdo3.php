<?php
require_once 'master_validation.php';
include 'lib/nangkoelib.php';
include_once 'lib/zLib.php';
include 'lib/zFunction.php';
echo open_body();
include 'master_mainMenu.php';
require_once('lib/zSelect2.php');
?>

<script language='javascript1.2' src='js/keu_pdo3.js?v=<?= time(); ?>'></script>
<script language='javascript' src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="'javascript'" src="js/zMaster.js"></script>
<script language='javascript' src=js/zTools.js></script>
<script language='javascript' src=js/zSearch.js></script>
<script languange='javascript1.2' src='js/formTable.js'></script>
<script language="javascript" src="js/zSelect2.js?v=<?php echo time(); ?>"></script>

<?php

OPEN_BOX('', '<span class=judul>' . getMenu('keu_pdo3') . '</span>');
$optunit = $optdivisi = $optthnsch = $optper = $optkeg = $optsat = $opttk = $optblok = $optpersch = $optbyr = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $optper .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}
for ($x = (-2); $x < 12; $x++) {
  $dt = mktime(0, 0, 0, date('m') - $x, 12, date('Y'));
  // $optper .= "<option value=" . date("Y-m", $dt) . ">" . date("Y-m", $dt) . "</option>";
}

$opttk .= "<option value=1>1</option>";

$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '" . $_SESSION['empl']['lokasitugas'] . "%'"
  . " and length(kodeorganisasi)<=6   order by kodeorganisasi asc "; //and tipe in ('AFDELING','KEBUN')
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $optdivisi .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
}
$str = "select * from " . $dbname . ".setup_kegiatan order by kodekegiatan asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $optkeg .= "<option value=" . $bar['kodekegiatan'] . ">" . $bar['kodekegiatan'] . " - " . $bar['namakegiatan'] . "</option>";
}
$str = "select * from " . $dbname . ".setup_satuan  order by satuan asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $optsat .= "<option value='" . $bar['satuan'] . "'>" . $bar['satuan'] . "</option>";
}
$str = "select distinct(substr(periode,1,4)) as tahun from " . $dbname . ".keu_pdoht where kodeorg='" . $_SESSION['empl']['lokasitugas'] . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $optthnsch .= "<option value='" . $bar['tahun'] . "'>" . $bar['tahun'] . "</option>";
}

$str = "select distinct(periode) as periode from " . $dbname . ".keu_pdoht order by periode desc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $optpersch .= "<option value='" . $bar['periode'] . "'>" . $bar['periode'] . "</option>";
}

$optunitpjd = '';
// if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
//     $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where (tipe='KEBUN' or tipe='PABRIK' or tipe='KANWIL' or tipe='HOLDING')  and induk!=''";
//     $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
//     $res->setFetchMode(PDO::FETCH_ASSOC);
//     $optgudang = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
//     while ($bar = $res->fetch()) {
//         $optunit .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
//         $optunitpjd .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
//     }
// } else {
//     $str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['empl']['lokasitugas'] . "'
//              order by kodeorganisasi asc ";
//     $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
//     $res->setFetchMode(PDO::FETCH_ASSOC);
//     while ($bar = $res->fetch()) {
//         $optunit .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
//         $optunitpjd .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['namaorganisasi'] . "</option>";
//     }
// }

$arrunit = array();
$arrunit = getOrgDetail(1);
foreach ($arrunit as $val => $nama) {
  $optunit .= "<option value='" . $val . "'>[" . $val . "]&nbsp;&nbsp;" . $nama . "</option>";
  $optunitpjd .= "<option value='" . $val . "'>[" . $val . "]&nbsp;&nbsp;" . $nama . "</option>";
}

$optrek = $optkas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select noakun,namaakun from " . $dbname . ".keu_5akun where noakun in ('1112101','1112102','1110101') and aktif=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $optkas .= "<option value='" . $bar['noakun'] . "'>" . $bar['namaakun'] . "</option>";
}

$str = "select * from " . $dbname . ".keu_5akunbank";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $wheredz = " kodebank='" . $bar['namabank'] . "'";
  $optnama = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank', $wheredz);
  $optrek .= "<option value='" . $bar['noakun'] . "'>" . $bar['rekening'] . " - " . $optnama[$bar['namabank']] . "</option>";
}

$res = array('1' => 'Pertama', '2' => 'Kedua');
foreach ($res as $key => $val) {
  $optbyr .= "<option value=" . $key . ">" . $val . "</option>";
}

echo "<div id=action_list>
        <table>
          <tr valign=middle>
            <td align=center style='width:70px;cursor:pointer;' onclick=newdata()> <img class=delliconBig src=images/skyblue/addbig.png title='" . $_SESSION['lang']['new'] . "'><br>" . $_SESSION['lang']['new'] . "</td>
            <td align=center style='width:70px;cursor:pointer;' onclick=displaylist()> <img class=delliconBig src=images/skyblue/list.png title='" . $_SESSION['lang']['list'] . "'><br>" . $_SESSION['lang']['list'] . "</td>
            <td>
              <fieldset>
                <legend>" . $_SESSION['lang']['find'] . "</legend>
                  <table>
                    <tr>
                      <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                      <td>:</td>
                      <td> <input type=text id=notransaksisch size=50 class='myinputtext sch' style=\"width:150px;\"> </td>

                      <td>Sesi</td>
                      <td>:</td>
                      <td><select class='select2 sch' id=sesisch style=\"width:150px;\">'" . $optbyr . "'</select></td>
                    </tr>
                    <tr>
                      <td>" . $_SESSION['lang']['unit'] . "</td>
                      <td>:</td>
                      <td> <select class='select2 sch' id=kodeorgsch style=\"width:154px;\">'" . $optunit . "'</select> </td>

                      <td>" . $_SESSION['lang']['periode'] . " Tutup Buku</td>
                      <td>:</td>
                      <td><select class='select2 sch' id=persch style=\"width:150px;\">'" . $optpersch . "'</select></td>
                    </tr>
                    <tr hidden>
                      <td>" . $_SESSION['lang']['tahun'] . "</td>
                      <td>:</td>
                      <td><select class='sch' id=thnsch style=\"width:150px;\">'" . $optthnsch . "'</select></td>
                    </tr>
                    <tr>
                      <td colspan=2></td>
                      <td colspan=3>
                        <button class=mybutton onclick=loaddata(0) >" . $_SESSION['lang']['find'] . "</button>
                        <button class=mybutton onclick=cancelsch() >" . $_SESSION['lang']['cancel'] . "</button>
                      </td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            <td>
              <fieldset>
                <legend>Generate Laporan PDO Seluruh Unit</legend>
                  <table>
                    <tr>
                      <td>" . $_SESSION['lang']['periode'] . " Tutup Buku</td>
                      <td>:</td>
                      <td><select class='select2 sch' id='periodeGenerate' style=\"width:150px;\">'" . $optpersch . "'</select></td>
                    </tr>
                    <tr>
                      <td colspan=2></td>
                      <td colspan=3>
                        <button class=mybutton onclick=\"generateAllPdo('pdf','event');\" >" . $_SESSION['lang']['pdf'] . "</button>
                        <button class=mybutton onclick=\"generateAllPdo('excel','event');\">" . $_SESSION['lang']['excel'] . "</button>
                      </td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            </tr>
          </table>";
CLOSE_BOX();
echo "</div>";

echo "<div id=listdata style=display:block>";
OPEN_BOX('', '<span class=judul>' . strtoupper($_SESSION['lang']['list']) . '</span>');
echo "<table cellpadding='7' cellspacing=1 border=0 class=sortable width=100%>
            <thead>
              <tr class=rowheader>
                <th align='center'>" . $_SESSION['lang']['nourut'] . "</th>
                <th align='center'>" . $_SESSION['lang']['notransaksi'] . "</th>
                <th align='center'>" . $_SESSION['lang']['unit'] . "</th>
                <th align='center'>" . $_SESSION['lang']['periode'] . "</th>
                <th align='center'>Sesi</th>
                <th align='center'>" . $_SESSION['lang']['approval_status'] . "</th>
                <th align='center'>" . $_SESSION['lang']['action'] . "</th>
              </tr>
            </thead>
            <tbody id=contain>
              <script>loaddata(0)</script>
            </tbody>
            <tfoot id=footData></tfoot>
          </table>";
CLOSE_BOX();
echo "</div>";

echo "<div id=header style=display:none>";
OPEN_BOX();

$optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrtipe = getOrgDetail(9);
foreach ($arrtipe as $kei => $fal) {
  $scek = selectQuery($dbname, 'setup_periodeakuntansi', '*', "kodeorg = '" . $kei . "' LIMIT 1");
  $rcek = fetchData($scek);
  if (count($rcek) != 0) {
    $optorg .= "<option value='" . $kei . "'>" . $fal . "</option>";
  }
}

echo "<fieldset>
        <legend>Header</legend>
        <table cellspacing=1 border=0>
          <tr>
            <td>No. PDO</td>
            <td>:</td>
            <td><input type=text id=nopdo disabled onkeypress='return tanpa_kutip(event)' class='myinputtext input-header' style=\"width:150px;\"></td>
          </tr>
          <tr>
            <td>" . $_SESSION['lang']['unit'] . "</td>
            <td>:</td>
            <td><select class='select2 input-header' id=unit style=\"width:154px;\">" . $optorg . "</select></td>
          </tr>
          <tr>
            <td>" . $_SESSION['lang']['periode'] . "</td>
            <td>:</td>
            <td><select class='select2 input-header' id=per style=\"width:154px;\">'" . $optper . "'</select></td>
          </tr>
          <tr>
            <td>Sesi</td>
            <td>:</td>
            <td><select class='select2 input-header' id=sesi style=\"width:154px;\">'" . $optbyr . "'</select></td>
          </tr>
          <tr>
            <td colspan=2></td>
            <td colspan=20>
              <button id=savehead class=mybutton onclick=savehead()>" . $_SESSION['lang']['save'] . "</button>
              <button id=batal class=mybutton onclick=cancel()>" . $_SESSION['lang']['cancel'] . "</button>
            </td>
          </tr>
        </table>
      </fieldset>";

CLOSE_BOX();
echo "</div>";

echo "<div id=detail style=display:none>";
OPEN_BOX();

$frm[0] = '';
$frm[0] .= "<fieldset><legend><b>Form " . $_SESSION['lang']['upah'] . "</b></legend>
              <table>
                <tr>
                    <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                    <td>:</td>
                    <td><input type=text id=noupah disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
                    <td hidden>" . $_SESSION['lang']['tipekaryawan'] . "</td>
                    <td hidden>:</td>
                    <td hidden><select id=tkupah style='width:150px;'>" . $opttk . "</select></td>
                </tr>
                    <td colspan=2></td>
                    <td colspan=100>
                      <button id=prevupah class=mybutton onclick=prevupah()>Generate</button>
                      <button id='generateExcelUpah' class=mybutton onclick=\"generateExcel('UPAH', 'event')\">Excel</button>
                      <button onclick=batalupah() class='mybutton btnBatal' name=btnBatal id=batalupah>" . $_SESSION['lang']['cancel'] . "</button>
                    </td>
                </tr>
              </table>
            </fieldset>";

$frm[0] .= "<div id='detailupah'> </div>";
$frm[0] .= "<div id='listupah'> </div>";

/* TAB 2 - PENGELUARAN TUNAI */

$frm[1] = '';
$frm[1] .= "<fieldset><legend><b>Form " . $_SESSION['lang']['pengeluarantunai'] . "</b></legend>
              <table>
                  <tr>
                      <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                      <td>:</td>
                      <td><input type=text id=nokas disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:150px;\"></td>
                  </tr>
                      <td colspan=2></td>
                      <td colspan=100>
                        <button id=prevkas class=mybutton onclick=prevkas()>Generate</button>
                        <button id='generateExcelKas' class=mybutton onclick=\"generateExcel('KAS', 'event')\">Excel</button>
                        <button onclick=batalkas() class='mybutton btnBatal' name=btnBatal id=batalkas>" . $_SESSION['lang']['cancel'] . "</button>
                      </td>
                  </tr>
              </table>
            </fieldset>";

$frm[1] .= "<div id='detailkas'> </div>";
$frm[1] .= "<div id='listkas'> </div>";

/* TAB 3 - KONTRAKTOR */

$frm[2] = '';
$frm[2] .= "<fieldset><legend><b>Form " . $_SESSION['lang']['kontraktor'] . "</b></legend>
              <table>
                <tr>
                  <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                  <td>:</td>
                  <td><input type=text id=nokontraktor disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:180px;\"></td>
                </tr>
                  <td colspan=2></td>
                  <td colspan=100>
                    <button id=prevkontraktor class=mybutton onclick=prevkontraktor()>Generate</button>
                    <button id='generateExcelKontraktor' class=mybutton onclick=\"generateExcel('KTRK', 'event')\">Excel</button>
                    <button onclick=batalkontraktor() class='mybutton btnBatal' name=btnBatal id=batalkontraktor>" . $_SESSION['lang']['cancel'] . "</button>
                  </td>
                </tr>
              </table>
            </fieldset>";

$frm[2] .= "<div id='detailkontraktor'> </div>";
$frm[2] .= "<div id='listkontraktor'> </div>";

/* TAB 3 - Supplier */

// $frm[3] = '';
// $frm[3] .= "<fieldset><legend><b>Form " . $_SESSION['lang']['supplier'] . "</b></legend>
//               <table>
//                 <tr>
//                   <td>" . $_SESSION['lang']['notransaksi'] . "</td>
//                   <td>:</td>
//                   <td><input type=text id=nosupplier disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:180px;\"></td>
//                 </tr>
//                   <td colspan=2></td>
//                   <td colspan=100>
//                     <button id=prevsupplier class=mybutton onclick=prevsupplier()>Generate</button>
//                     <button onclick=batalsupplier() class='mybutton btnBatal' name=btnBatal id=btnBatalsupplier>" . $_SESSION['lang']['cancel'] . "</button>
//                   </td>
//                 </tr>
//               </table>
//             </fieldset>";

// $frm[3] .= "<div id='detailsupplier'> </div>";
// $frm[3] .= "<div id='listsupplier'> </div>";

/* TAB 3 - Hutang Kas */

$frm[3] = '';
$frm[3] .= "<fieldset><legend><b>Form Hutang Kas</b></legend>
              <table>
                <tr>
                  <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                  <td>:</td>
                  <td><input type=text id=nohutangkas disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:180px;\"></td>
                </tr>
                  <td colspan=2></td>
                  <td colspan=100>
                    <button id=prevhutangkas class=mybutton onclick=prevhutangkas()>Generate</button>
                    <button id='generateExcelHutangkas' class=mybutton onclick=\"generateExcel('HTGKAS', 'event')\">Excel</button>
                    <button onclick=batalhutangkas() class='mybutton btnBatal' name=btnBatal id=batalhutangkas>" . $_SESSION['lang']['cancel'] . "</button>
                  </td>
                </tr>
              </table>
            </fieldset>";

$frm[3] .= "<div id='detailhutangkas'> </div>";
$frm[3] .= "<div id='listhutangkas'> </div>";

/* TAB 4 - Perjalanan Dinas */

$frm[4] = '';
$frm[4] .= "<fieldset><legend><b>Form Perjalanan Dinas</b></legend>
              <table>
                <tr>
                  <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                  <td>:</td>
                  <td><input type=text id=nopjd disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:180px;\"></td>
                </tr>
                  <td colspan=2></td>
                  <td colspan=100>
                    <button id=prevpjd class=mybutton onclick=prevpjd()>Generate</button>
                    <button id='generateExcelPjd' class=mybutton onclick=\"generateExcel('PJD', 'event')\">Excel</button>
                    <button onclick=batalpjd() class='mybutton btnBatal' name=btnBatal id=batalpjd>" . $_SESSION['lang']['cancel'] . "</button>
                  </td>
                </tr>
              </table>
            </fieldset>";

$frm[4] .= "<div id='detailpjd'> </div>";
$frm[4] .= "<div id='listpjd'> </div>";

/* TAB 5 - PMK Lainnya */

$frm[5] = '';
$frm[5] .= "<fieldset><legend><b>Form PMK Lainnya</b></legend>
              <table>
                <tr>
                  <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                  <td>:</td>
                  <td><input type=text id=noothers disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:180px;\"></td>
                </tr>
                  <td colspan=2></td>
                  <td colspan=100>
                    <button id=prevothers class=mybutton onclick=prevothers()>Generate</button>
                    <button id='generateExcelOthers' class=mybutton onclick=\"generateExcel('OTH', 'event')\">Excel</button>
                    <button onclick=batalothers() class='mybutton btnBatal' name=btnBatal id=batalothers>" . $_SESSION['lang']['cancel'] . "</button>
                  </td>
                </tr>
              </table>
            </fieldset>";

$frm[5] .= "<div id='detailothers'> </div>";
$frm[5] .= "<div id='listothers'> </div>";

/* TAB 6 - Tanaman */

$frm[6] = '';
$frm[6] .= "<fieldset style='display:none'><legend><b>Form PMK Tanaman</b></legend>
              <table>
                <tr>
                  <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                  <td>:</td>
                  <td><input type=text id=notanaman disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:180px;\"></td>
                </tr>
                  <td colspan=2></td>
                  <td colspan=100>
                    <button id=prevtanaman class=mybutton onclick=prevtanaman()>Generate</button>
                    <button onclick=bataltanaman() class='mybutton btnBatal' name=btnBatal id=bataltanaman>" . $_SESSION['lang']['cancel'] . "</button>
                  </td>
                </tr>
              </table>
            </fieldset>";

$frm[6] .= "<div style='display:none' id='detailtanaman'> </div>";
$frm[6] .= "<div style='display:none' id='listtanaman'> </div>";

/* TAB 7 - PMK Traksi */

$frm[7] = '';
$frm[7] .= "<fieldset style='display:none'><legend><b>Form PMK Traksi</b></legend>
              <table>
                <tr>
                  <td>" . $_SESSION['lang']['notransaksi'] . "</td>
                  <td>:</td>
                  <td><input type=text id=notraksi disabled  onkeypress='return tanpa_kutip(event)' class=myinputtext style=\"width:180px;\"></td>
                </tr>
                  <td colspan=2></td>
                  <td colspan=100>
                    <button id=prevtraksi class=mybutton onclick=prevtraksi()>Generate</button>
                    <button onclick=bataltraksi() class='mybutton btnBatal' name=btnBatal id=bataltraksi>" . $_SESSION['lang']['cancel'] . "</button>
                  </td>
                </tr>
              </table>
            </fieldset>";

$frm[7] .= "<div style='display:none' id='detailtraksi'> </div>";
$frm[7] .= "<div style='display:none' id='listtraksi'> </div>";

/* input-headerER TAB */

$hfrm[0] = strtoupper($_SESSION['lang']['upah']);
$hfrm[1] = strtoupper($_SESSION['lang']['pengeluarantunai']);
$hfrm[2] = strtoupper($_SESSION['lang']['kontraktor']);
// $hfrm[3] = strtoupper($_SESSION['lang']['supplier']);
$hfrm[3] = strtoupper("Hutang Kas");
$hfrm[4] = strtoupper($_SESSION['lang']['perjalanandinas']);
$hfrm[5] = strtoupper("PMK Lainnya");
// $hfrm[6] = strtoupper("PMK Tanaman");
// $hfrm[7] = strtoupper("PMK Traksi");

drawTab('FRM', $hfrm, $frm, 170, 'auto');

CLOSE_BOX();

echo "</div>";
echo close_body();
?>