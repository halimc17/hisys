<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_panen extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_datakaryawan');
        $this->load->model('Mpanen');
        $this->load->model('Setup_datakaryawan');
        $this->load->model('Privilege', 'priv');
    }

    function saveDetail($params, $val)
    {
        $this->Mpanen->saveEditDtl($params, $val);
    }

    function postingData($where)
    {
        $this->Mpanen->postingPanen($where);
    }

    function unSyncData($where)
    {
        $this->Mpanen->unpostingSync($where);
    }

    function unposting($where)
    {
        $this->Mpanen->unpostingPanen($where);
    }

    function updateJjg($where, $val)
    {
        $this->Mpanen->updateJJG($where, $val);
    }

    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'pdf':
                $this->load->lib('Mpdf', 'pdf');
                $mpdf = $this->lib->pdf->create();
                // break;
            case 'excel':
            case 'csv':
            default:
                // $wLT = $_SESSION['standard']['userid'];
                $wLT = $_SESSION['empl']['lokasitugas'];
                $page = (int)$this->get('page');
                // $qLT = $this->Mpanen->loctugas($wLT);
                // $lt = $qLT[0]['kodeorg'];
                ////////////////////////////
                //Pengambilan Data Total Row
                $dataWhere = array();
                $datasearch = array('notransaksi', 'kodeorg', 'divisi', 'periode');
                foreach ($datasearch as $sh) {
                    $Where = false;
                    if (!empty($this->get($sh))) {
                        $Where = " AND {$sh} = '" . $this->get($sh) . "'";
                        if ($sh == 'periode') {
                            $Where = " AND tanggal LIKE '" . $this->get($sh) . "%'";
                        }
                    } else {
                        if ($sh == 'kodeorg') {
                            $Where = " AND {$sh} = " . "'$wLT'";
                            // $Where = " AND {$sh} = " . "'SD1E'";
                        }
                    }
                    if ($Where) {
                        $dataWhere[] = $Where;
                    }
                }
                if (!empty($this->get('tanggal1'))) {
                    $opt = "=";
                    $tgl2 = "";
                    if (!empty($this->get('tanggal2'))) {
                        $opt = "BETWEEN";
                        $tgl2 = " AND '" . date("Y-m-d", strtotime($this->get('tanggal2'))) . "'";
                    }
                    $dataWhere[] = " AND tanggal " . $opt . " '" . date("Y-m-d", strtotime($this->get('tanggal1'))) . "'" . $tgl2;
                }
                // var_dump($dataWhere);

                $getData = $this->Mpanen->aktifitas($dataWhere);
                //Pagination setup and load
                $this->load->lib("Pagination", "paging");
                $tab = $this->lib->paging;
                $tab->id = "Panen";
                $tab->total_rows = $getData->rowCount();
                //row has definition
                $tab->per_page = 30; //LIMIT : default 20
                $tab->cur_page = $page;
                $starting_limit = ($page - 1) * $tab->per_page;
                //get Data per page == 
                $dataTable = $this->getTable($this->Mpanen->aktifitas($dataWhere, [$tab->cur_page, $tab->per_page]));
                //create HTML to json
                $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                //Build HTML
                $tab->build();

                if ($case == 'pdf') {
                    $mpdf->WriteHTML($tab->getHTML()->forPDF);
                    $mpdf->Output();
                } else {
                    //Load HTML 
                    $tab->loadHTML();
                    //Process End 
                }
                break;
            case 'form':
                $this->formFormat();
                break;
            case 'savedetail':
                $notransaksi = $this->get('notransaksi');
                $kodeorg = $this->get('kodeorg');
                $tph = $this->get('tph');
                $sesi = $this->get('sesi');
                $idjenis = $this->get('idjenis');
                $jenisval = $this->get('mhvalue');
                // echo $jenisval;
                $arrParams = array(
                    'notransaksi' => $notransaksi,
                    'kodeorg' => $kodeorg,
                    'tph' => $tph,
                    'sesi' => $sesi,
                    'kodedenda' => $idjenis
                );
                $this->saveDetail($arrParams, $jenisval);
                break;
            case 'updateTotJjg':
                $notransaksi = $this->get('notransaksi');
                $kodeorg = $this->get('kodeorg');
                $tph = $this->get('tph');
                $sesi = $this->get('sesi');
                $tot = $this->get('totJjg');
                $whr = "notransaksi = '$notransaksi' and kodeorg = '$kodeorg' and tph = '$tph' and sesi = '$sesi'";
                $this->updateJjg($whr, $tot);
                break;
            case 'view':
                $notxn = $this->get('id');
                $mode = $this->get('viewmode');
                $tgl = $this->get('tgl');
                $unit = $this->get('unit');
                $mutuH = $this->Mpanen->kodeMutu();
                $dataDtl = $this->Mpanen->getDetailData($notxn);
                $this->getView($notxn, $tgl, $unit, $dataDtl, $mutuH, $mode);
                break;
            case 'Filter':
                $this->filter_panen();
                break;
            case 'posting':
                $notransaksi = $this->get('notransaksi');
                $kodeorg = $this->get('kodeorg');
                $gang = $this->get('gangcode');
                $nikmandor = $this->get('nikmandor');
                $whr = "notransaksi = '$notransaksi' and kodeorg = '$kodeorg' and gangcode= '$gang' and nikmandor = '$nikmandor'";
                $this->postingData($whr);
                break;
            case 'unSync':
                $notransaksi = $this->get('notransaksi');
                $kodeorg = $this->get('kodeorg');
                $whr = "notransaksi = '$notransaksi' and kodeorg = '$kodeorg'";
                $this->unSyncData($whr);
                break;
            case 'unposting':
                $notransaksi = $this->get('notransaksi');
                $kodeorg = $this->get('kodeorg');
                $gang = $this->get('gangcode');
                $nikmandor = $this->get('nikmandor');
                $whr = "notransaksi = '$notransaksi' and kodeorg = '$kodeorg' and gangcode= '$gang' and nikmandor = '$nikmandor'";
                $this->unposting($whr);
                break;
            case 'insert':
                break;
        }
    }

    function getView($id, $tgl, $unit, $dtl, $kodemutu, $mode)
    {
        // print_r($_SESSION);
        $kmLen = count($kodemutu);
?> <style>
            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
            }
        </style>
        <fieldset style='min-height:100%;padding:30px;margin:0'>
            <table cellpadding=3 cellspacing=1 class=sortable>
                <tbody class=rowcontent>
                    <tr>
                        <td>Kode Organisasi</td>
                        <td> :</td>
                        <td><b><? echo $unit ?></b></td>
                    </tr>
                    <tr>
                        <td>No Transaksi</td>
                        <td> :</td>
                        <td><b><? echo $id ?></b></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td> :</td>
                        <td><b><? echo $tgl ?></b></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Prestasi</p>
            </br>
            <!-- <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">PRESTASI</p> -->
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td rowspan="2" align=center>No</td>
                        <td rowspan="2" align=center>No Transaksi</td>
                        <td rowspan="2" align=center>Nama Pemanen</td>
                        <td rowspan="2" align=center>Blok</td>
                        <td rowspan="2" align=center>Tph</td>
                        <td rowspan="2" align=center>Sesi</td>
                        <!-- <td rowspan="2" align=center>Tahun tanam</td> -->
                        <td rowspan="2" align=center>Photo Jjg</td>
                        <td rowspan="2" align=center>Photo Jjg AI</td>
                        <!-- <td rowspan="2" align=center>Photo akhir</td> -->
                        <td colspan="3" align="center">HASIL KERJA</td>
                        <td colspan="<?= $kmLen ?>" align="center">Mutu Buah</td>
                        <?
                        if (strcmp($mode, 'edit') == 0) {
                        ?>
                            <td rowspan="3" align=center>Action</td>
                        <?
                        }
                        ?>
                    </tr>
                    <tr>
                        <td align=center>Jjg</td>
                        <td align=center>Jjg AI</td>
                        <td align=center>Brondolan</td>
                        <?
                        foreach ($kodemutu as $key => $value) {
                        ?>
                            <td id="<?= $value['kodedenda'] ?>" align=center style="width:5%"><? echo $value['kriteria'] ?></td>
                        <?
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    if (count($dtl['prestasi']) > 0) {
                        foreach ($dtl['prestasi'] as $key => $value) {
                            $nomor++;
                            // print_r($value);
                            $whrQty = "where notransaksi = '{$value['notransaksi']}' and kodeorg = '{$value['kodeorg']}' and tph = '{$value['tph']}' and 
                            sesi = '{$value['sesi']}'";
                            $nm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '{$value['nik']}'");
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $nomor ?></td>
                                <td id="notransid<?= $nomor ?>" align=center><? echo $value['notransaksi'] ?></td>
                                <td id="pemanenNm<?= $nomor ?>" align=center><? echo $nm ?></td>
                                <td id="kodeorgid<?= $nomor ?>" align=center><? echo $value['kodeorg'] ?></td>
                                <td id="tphid<?= $nomor ?>" align=center><? echo $value['tph'] ?></td>
                                <td id="idsesi<?= $nomor ?>" align=center><? echo $value['sesi'] ?></td>
                                <td align=center><img title='Foto' class='resiconn' style='width:20px;height:20px;' src=<? echo $value['photo'] ?>></td>
                                </td>
                                <td align=center><img title='Foto' class='resiconn' style='width:20px;height:20px;' src=<? echo $value['photo2'] ?>></td>
                                </td>
                                <td id="hasilkerja<?= $nomor ?>" align=center><? echo $value['hasilkerja'] ?></td>
                                <td id="hasilkerja<?= $nomor ?>" align=center><? echo $value['janjang_ai'] ?></td>
                                <td align=center><? echo $value['brondolan'] ?></td>
                                <?
                                $mhVal = 0;
                                foreach ($kodemutu as $key => $value) {
                                    $qty = $this->Mpanen->getMutuQty($whrQty . " and kodedenda = '{$value['kode']}'");
                                    $mhVal++;
                                ?>
                                    <td align=center style="width:5%"><input type="number" onchange="onchangeinput('<?= $nomor ?>', '<?= $kmLen ?>')" onwheel="return false;" id="inputDtlrw<?= $nomor . $mhVal ?>" maxlength="2" style="width:100%" value="<?= $qty ?>" disabled></td>
                                <?
                                }
                                ?>
                                <?
                                if (strcmp($mode, 'edit') == 0) {
                                ?>
                                    <td align=center>
                                        <button id="editDtlBtn<?= $nomor ?>" title="ubah" type="submit" onclick="editDtl(<?= $nomor ?>)"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                                        <button id="deleteDtlBtn<?= $nomor ?>" title="hapus" type="submit" onclick="deleteDtl()"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                        <button id="saveDtlBtn<?= $nomor ?>" title="simpan" type="submit" onclick="saveDtl(<?= $nomor ?>)" style="display:none"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                                        <button id="cancelDtlBtn<?= $nomor ?>" title="batal" type="submit" onclick="cancelEditDtl(<?= $nomor ?>)" style="display:none"><i class="fa fa-times" aria-hidden="true"></i></button>
                                    </td>
                                <?
                                }
                                ?>

                            </tr>
                        <? } ?>
                    <? } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <?
                            $totRw = count($kodemutu) + 13;
                            if (strcmp($mode, 'edit') == 0) {
                            ?>
                                <td colspan=<?= $totRw ?> style="text-align: center;">Data Tidak Ditemukan</td>
                            <?
                            } else {
                            ?>
                                <td colspan=<?= $totRw - 1 ?> style="text-align: center;">Data Tidak Ditemukan</td>
                            <?
                            } ?>
                        </tr>
                    <? }
                    ?>
                </tbody>
            </table>
            </br>
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Rekap</p>
            </br>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td rowspan="2" align=center>No Induk Karyawan</td>
                        <td rowspan="2" align=center>Nama</td>
                        <td rowspan="2" align=center>Blok</td>
                        <td rowspan="2" align=center>Hasil Kerja (JJG)</td>
                        <td rowspan="2" align=center>Luas</td>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    if (count($dtl['rekap']) > 0) {
                        foreach ($dtl['rekap'] as $key => $value) {
                            $nomor++;
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $value['nik'] ?></td>
                                <td id="kodeorgid<?= $nomor ?>" align=center><? echo $value['nama'] ?></td>
                                <td id="notransid<?= $nomor ?>" align=center><? echo "DIVISI " . substr($value['kodeorg'], 4, 2) . " BLOK " . substr($value['kodeorg'], 6, 4) ?></td>
                                <td id="pemanenNm<?= $nomor ?>" align=center><? echo $value['hasilkerja'] ?></td>
                                <td id="tphid<?= $nomor ?>" align=center><? echo $value['luas_aktual'] ?></td>
                            </tr>
                        <? } ?>
                    <? } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td colspan=17 style="text-align: center;">Data Tidak Ditemukan</td>
                        </tr>
                    <? }
                    ?>
                </tbody>
            </table>
            </br>
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Verifikasi</p>
            </br>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td rowspan="2" align=center>No</td>
                        <td rowspan="2" align=center>No Transaksi</td>
                        <td rowspan="2" align=center>Nama Pemanen</td>
                        <td rowspan="2" align=center>Blok</td>
                        <td rowspan="2" align=center>Tph</td>
                        <td rowspan="2" align=center>Sesi</td>
                        <!-- <td rowspan="2" align=center>Tahun tanam</td> -->
                        <td rowspan="2" align=center>Photo Jjg</td>
                        <td rowspan="2" align=center>Photo Jjg AI</td>
                        <!-- <td rowspan="2" align=center>Photo akhir</td> -->
                        <td colspan="3" align="center">HASIL KERJA</td>
                        <td colspan="<?= $kmLen ?>" align="center">Mutu Buah</td>
                        <?
                        if (strcmp($mode, 'edit') == 0) {
                        ?>
                            <td rowspan="2" align=center>Action</td>
                        <?
                        }
                        ?>
                    </tr>
                    <tr>
                        <td align=center>Jjg</td>
                        <td align=center>Jjg AI</td>
                        <td align=center>Brondolan</td>
                        <?
                        foreach ($kodemutu as $key => $value) {
                        ?>
                            <td align=center style="width:5%"><? echo $value['kriteria'] ?></td>
                        <?
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    if (count($dtl['verifikasi']) > 0) {
                        foreach ($dtl['verifikasi'] as $key => $value) {
                            $nomor++;
                            // print_r($value);
                            // $blk = substr($value['kodeorg'], 0, 9);
                            $whrQtyVerif = "where notransaksi = '{$value['notransaksi']}' and kodeorg = '{$value['kodeorg']}' and tph = '{$value['tph']}' and 
                            sesi = '{$value['sesi']}'";
                            $nm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '{$value['nik']}'");
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $nomor ?></td>
                                <td id="notransidverif<?= $nomor ?>" align=center><? echo $value['notransaksi'] ?></td>
                                <td id="pemanenNmverif<?= $nomor ?>" align=center><? echo $nm ?></td>
                                <td id="kodeorgidverif<?= $nomor ?>" align=center><? echo $value['kodeorg'] ?></td>
                                <td id="tphidverif<?= $nomor ?>" align=center><? echo $value['tph'] ?></td>
                                <td id="idsesiverif<?= $nomor ?>" align=center><? echo $value['sesi'] ?></td>
                                <td align=center><img title='Foto' class='resiconn' style='width:20px;height:20px;' src=<? echo $value['photo'] ?>></td>
                                </td>
                                <td align=center><img title='Foto' class='resiconn' style='width:20px;height:20px;' src=<? echo $value['photo2'] ?>></td>
                                </td>
                                <td id="hasilkerjaverif<?= $nomor ?>" align=center><? echo $value['hasilkerja'] ?></td>
                                <td id="hasilkerjaverif<?= $nomor ?>" align=center><? echo $value['janjang_ai'] ?></td>
                                <td align=center><? echo $value['brondolan'] ?></td>
                                <?
                                $mhValVerif = 0;
                                foreach ($kodemutu as $key => $value) {
                                    $qtyVerif = $this->Mpanen->getMutuQty($whrQtyVerif . " and kodedenda = '{$value['kode']}'");
                                    $mhValVerif++;
                                ?>
                                    <td align=center style="width:5%"><input type="number" onchange="onchangeinput('<?= $nomor ?>', '<?= $kmLen ?>')" onwheel="return false;" id="inputDtlrwverif<?= $nomor . $mhValVerif ?>" maxlength="2" style="width:100%" value="<?= $qtyVerif ?>" disabled></td>
                                <?
                                }
                                ?>
                                <?
                                if (strcmp($mode, 'edit') == 0) {
                                ?>
                                    <td align=center>
                                        <button id="editDtlBtnverif<?= $nomor ?>" title="ubah" type="submit" onclick="editDtl(<?= $nomor ?>)"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                                        <button id="deleteDtlBtnverif<?= $nomor ?>" title="hapus" type="submit" onclick="deleteDtl()"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                        <button id="saveDtlBtnverif<?= $nomor ?>" title="simpan" type="submit" onclick="saveDtl(<?= $nomor ?>)" style="display:none"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                                        <button id="cancelDtlBtnverif<?= $nomor ?>" title="batal" type="submit" onclick="cancelEditDtl(<?= $nomor ?>)" style="display:none"><i class="fa fa-times" aria-hidden="true"></i></button>
                                    </td>
                                <?
                                }
                                ?>

                            </tr>
                        <? } ?>
                    <? } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <?
                            $totRw = count($kodemutu) + 13;
                            if (strcmp($mode, 'edit') == 0) {
                            ?>
                                <td colspan=<?= $totRw ?> style="text-align: center;">Data Tidak Ditemukan</td>
                            <?
                            } else {
                            ?>
                                <td colspan=<?= $totRw - 1 ?> style="text-align: center;">Data Tidak Ditemukan</td>
                            <?
                            } ?>
                        </tr>
                    <? }
                    ?>
                </tbody>
            </table>
        </fieldset>

    <?
    }

    function filter_panen()
    {
        // echo "TEST :".$this->get('notransaksi');
        $whereOpt = "Where kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "' AND tipetransaksi = 'PNN' ";
        $this->load->lib('MakeHTML', 'mkhtml');
    ?>
        <form method="GET">
            <div class="body-frame u-margin-10">
                <div class="row">
                    <div class="col-xl-8 col-md-8 col-sm-8 col-xs-12 u-margin-b-10">
                        <label>No Transaksi</label>
                        <input class="full-width" type="text" name="notransaksi" onchange="" value="<? echo $this->get('notransaksi'); ?>" title="No Transaksi" placeholder="00000000000000-00">
                    </div>
                    <div class="col-xl-4 col-md-4 col-sm-6 col-xs-12 u-margin-b-10">
                        <label>Periode</label>
                        <select class="full-width" name="periode" onchange="" title="Periode" search="false">
                            <? echo $this->lib->mkhtml->options($this->Mpanen->getPeriode($whereOpt), $this->get('periode')); ?>
                        </select>
                    </div>
                    <div class="col-xl-8 col-md-8 col-sm-8 col-xs-12 u-margin-b-10">
                        <label>Kode Organisasi</label>
                        <select class="full-width" name="kodeorg" onchange="" title="Kode Organisasi" search="true">
                            <? echo $this->lib->mkhtml->options($this->Mpanen->getUnit($whereOpt), $this->get('kodeorg')); ?>
                        </select>
                    </div>
                    <div class="col-xl-4 col-md-4 col-sm-6 col-xs-12 u-margin-b-10">
                        <label>Divisi</label>
                        <select class="full-width" name="divisi" onchange="" title="Divisi" search="false">
                            <? echo $this->lib->mkhtml->options($this->Mpanen->getDivisi($whereOpt), $this->get('divisi')); ?>
                        </select>
                    </div>
                    <div class="col-xl-6 col-md-6 col-sm-6 col-xs-12 u-margin-b-10">
                        <label>Tanggal Mulai</label>
                        <input class="full-width" type="text" name="tanggal1" value="<? echo $this->get('tanggal1'); ?>" readonly="readonly" onmousemove="setCalendar(this,'%d-%m-%Y')" autocomplete="off" title="Tanggal Mulai">
                    </div>
                    <div class="col-xl-6 col-md-6 col-sm-6 col-xs-12 u-margin-b-10">
                        <label>Tanggal Selesai</label>
                        <input class="full-width" type="text" name="tanggal2" value="<? echo $this->get('tanggal2'); ?>" readonly="readonly" onmousemove="setCalendar(this,'%d-%m-%Y')" autocomplete="off" title="Tanggal Akhir">
                    </div>

                </div>
                <div class="clearfix"></div>
            </div>
            <div class="body-frame u-margin-10">
                <div class="row">
                    <div class="col-12 u-margin-b-10">
                        <input class="mybutton col-3 col-md-3 col-sm-3 col-xs-3 u-margin-r-10" type="submit" placeholder="" value="Submit">
                        <input class="mybutton col-3 col-md-3 col-sm-3 col-xs-3" type="reset" placeholder="" value="Clear" onclick="reset();return false;">
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>

        </form>


    <?
    }
    function getTable($data)
    {

        // No.	No Transaksi	No. Referensi	Unit	Kemandoran / Vendor	Tanggal	Jjg	Mandor	Mandor 1	Asisten	Approval	Kerani Panen	Last Update	Tanggal Input	Tanggal Stages	Status Stages	Posting By	Tanggal Posting	Aksi
        $thead =  "<thead>
                    <tr class=\"rowheader\">
                        <th align=\"center\">No</th>
                        <th align=\"left\">Transaksi</th>
                        <th align=\"center\">Unit</th>
                        <th align=\"center\">Kemandoran By</th>
                        <th align=\"center\">Mandor</th>	
                        <th align=\"center\">Mandor 1</th>
                        <th align=\"center\">Asisten</th>
                        <th align=\"center\">Processed</th>
                        <th align=\"center\">Kerani</th>
                        <th align=\"center\">Device Id</th>
                        <th align=\"center\">Last Update</th>
                        <th align=\"center\">Pembuat</th>
                    </tr>
                </thead>";
        $datakar = $this->Setup_datakaryawan->selectOpt();
        $n = 1;
        $dataTable = "";
        if ($data->rowCount() > 0) {
            while ($v = $data->fetch()) {
                $act = array();
                $act['view'] = "listAction('?switcher=view&id={$v->notransaksi}&viewmode=view&unit={$v->kodeorg}&tgl={$v->tanggal}');";
                // $act['edit'] = "listAction('?switcher=view&id={$v->notransaksi}&viewmode=edit&unit={$v->kodeorg}&tgl={$v->tanggal}');"; //update
                $gangcdNm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '$v->gangcode'");
                $mandorNm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '$v->nikmandor'");
                $mandor1Nm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '$v->nikmandor1'");
                $asistenNM = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '$v->nikasisten'");
                $keraniNm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '$v->kerani'");
                $creator = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '$v->createby'");
                if ($this->priv->imAdmin()) {
                    if ($v->jurnal == 1) {
                        $act['unposting'] = "callback=unpostingKebun({$n});"; //unposting
                    } else if ($v->syn == 1 && $v->flag == 1) {
                        $act['cancel'] = "callback=unpostingSync({$n});"; //posting
                    } else {
                        // $act['delete'] = "listAction('?switcher=delete&id={$v->notransaksi}');";//delete
                        $act['posting'] = "callback=postingKebun({$n});"; //posting
                        // echo $act['posting'];
                        // $act['update'] = "listAction('?switcher=update&id={$v->notransaksi}');";//posting
                    }
                }
                $action = $this->toAtrr($act);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">" . $n . "</td>";
                $dataTable .= "<td id=\"tmtxn$n\" align=\"left\">" . $v->notransaksi . "</td>";
                $dataTable .= "<td id=\"tmorgcd$n\" align=\"center\">" . $v->kodeorg . "</td>";
                $dataTable .= "<td id=\"tmgangcd$n\" align=\"center\">" . $gangcdNm . "</td>";
                $dataTable .= "<td id=\"tmnikmandor$n\" align=\"center\">" . $mandorNm . "</td>";
                $dataTable .= "<td align=\"center\">" . $mandor1Nm . "</td>";
                $dataTable .= "<td align=\"center\">" . $asistenNM . "</td>";
                $dataTable .= "<td align=\"center\">" . $v->flag . "</td>";
                $dataTable .= "<td align=\"center\">" . $keraniNm . "</td>";
                $dataTable .= "<td align=\"center\">" . $v->deviceid . "</td>";
                $dataTable .= "<td align=\"center\">" . date("d F Y", strtotime($v->updatetime)) . "</td>";
                $dataTable .= "<td align=\"center\">{$creator}</td>";
                // $dataTable .= "<td align=\"center\">{$datakar[$v->createby]}</td>";
                $dataTable .= "</tr>";
                $n++;
            }
        } else {
            $dataTable .= "<tr>";
            $dataTable .= "<td align=\"center\" colspan=\"12\">No data</td>";
            $dataTable .= "</tr>";
        }
        $result['head'] = $thead;
        $result['body'] = $dataTable;
        return (object)$result;
    }
    function formFormat()
    {
    ?>
        <div class="body-frame u-margin-10">
            <form method="POST" action="<? echo $this->site_url() . $this->uri->uri_string; ?>?switcher=<? echo $switcher; ?>" callback="pascaSubmit">
                <div class="row">
                    <div class="col-xl-4 col-md-4 col-sm-6 col-xs-12 u-margin-b-10">
                        <label>Document</label>
                        <select class="col-4" name="tanggal" onchange="" title="Tanggal" required="" search="true"></select>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-xl-4 col-md-4 col-sm-6 col-xs-12 u-margin-b-10">
                        <input class="mybutton" type="submit" placeholder="" value="Submit">
                    </div>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>

<?
    }

    function options($SELF, $breadcrumb)
    {
        $option = array();
        $option['master']       = '#bodymaster';
        $option['slave']        = $this->site_url() . $this->uri->uri_string . "_slave";
        $option['search']       = $_GET;
        $option['getpage']      = 'switcher';
        $option['type']         = '';
        $option['javascript']['src'] = array($this->base_url() . 'js/' . $SELF . '.js?version=' . time() . '');

        $d = array();
        $d['title'] = "Form Entry Data";
        $d['slave'] = "form";
        $d['text'] = "new";
        $d['show'] = false;
        $d['isEnable'] = true;
        $option['buatbaru'] = $d;

        $d = array();
        $d['title'] = "List Data";
        $d['text'] = "List Data";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['listdata'] = $d;

        $d = array();
        $d['title'] = "Filter";
        $d['slave'] = "Filter";
        $d['text'] = "Filter";
        $d['width'] = "300px";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['filter'] = $d;

        $option['breadcrumb']['title'] = $breadcrumb;

        $option['excel']['show'] = true;
        $option['pdf']['show'] = true;
        $option['csv']['show'] = true;
        $option['fixHeader']['show'] = false;
        $option['actions'] = array();
        $option['pathinfo']['site_url'] = $this->site_url();
        $option['pathinfo']['base_url'] = $this->base_url();
        $OPT =  json_encode($option);
        return $OPT;
    }
}
?>