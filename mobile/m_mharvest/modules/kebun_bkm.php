<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_bkm extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_datakaryawan');
        $this->load->model('M_rawat');
        $this->load->model('Bkm');
        $this->load->model('Privilege', 'priv');
    }
    function deleteAllDetail($notransaksi)
    {
        $whr = "notransaksi='" . $notransaksi . "'";
        $this->M_rawat->deleteAlldetailRawat($whr);
    }
    function postingTransaksi($notransaksi)
    {
        $whr = "notransaksi='" . $notransaksi . "'";
        $this->M_rawat->postingBkm($whr);
    }
    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'deleteAll':
                $notransaksi = $this->get('notransaksi');
                $this->deleteAllDetail($notransaksi);
                break;
            case 'posting':
                $notransaksi = $this->get('notransaksi');
                $this->postingTransaksi($notransaksi);
                break;
            case 'pdf':
            case 'excel':
            case 'csv':
            default:
                // $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
                $page = (int)$this->get('page');
                ////////////////////////////
                //Pengambilan Data Total Row
                // $whr = 'WHERE tipetransaksi="BKM" ORDER BY TANGGAL DESC';
                $whr = 'WHERE kodeorg="' . $_SESSION['empl']['lokasitugas'] . '" and tipetransaksi="BKM" ORDER BY TANGGAL DESC';
                $getData = $this->M_rawat->getAktifitas($whr);
                if ($getData and $getData->rowCount() > 0) {
                    //Pagination setup and load
                    $this->load->lib("Pagination", "paging");
                    $tab = $this->lib->paging;
                    $tab->id = "Bkm";
                    $tab->total_rows = $getData->rowCount();
                    //row has definition
                    $tab->per_page = 30; //LIMIT : default 20
                    $tab->cur_page = $page;
                    $starting_limit = ($page - 1) * $tab->per_page;
                    //get Data per page == 


                    $dataTable = $this->getTable($this->M_rawat->getAktifitas($whr, [$tab->cur_page, $tab->per_page]));
                    //create HTML to json
                    $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                    $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                    //Build HTML
                    $tab->build();
                    //Load HTML  
                    $tab->loadHTML();
                    //Process End
                } else {
                    $this->load->lib("Pagination", "paging");
                    $tab = $this->lib->paging;
                    $tab->id = "Bkm";
                    $tab->total_rows = $getData->rowCount();
                    //row has definition
                    $tab->per_page = 30; //LIMIT : default 20
                    $tab->cur_page = $page;
                    $starting_limit = ($page - 1) * $tab->per_page;
                    //get Data per page == 


                    $dataTable = $this->getTable($this->M_rawat->getAktifitas($whr, [$tab->cur_page, $tab->per_page]));
                    //create HTML to json
                    $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                    $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                    //Build HTML
                    $tab->build();
                    //Load HTML  
                    $tab->loadHTML();
                }
                break;
            case 'form':
                $this->formFormat();
                break;
            case 'view':
                $notransaksi = $this->get('notransaksi');
                $orgcd = $this->get('orgcd');
                $whr = "where notransaksi='" . $notransaksi . "'";
                // $getPrestasi = $this->M_rawat->getDetailPrestasi('where notransaksi="' . $notransaksi . '"');
                $getMaterial = $this->M_rawat->getDetailMaterial($whr);
                $getPrestasi = $this->M_rawat->getDetailPrestasi2($whr);
                $getKehadiran = $this->M_rawat->getDetailKehadiran($whr);
                $this->getView($notransaksi, $getKehadiran, $getPrestasi, $getMaterial, $orgcd);

                break;
            case 'Filter':
                break;
            case 'insert':
                break;
        }
    }

    function getView($notransaksi, $dataKehadiran, $dataPrestasi, $getMaterial, $kodeorg)
    {
        function pre_arr($arr)
        {
            echo "<pre>";
            echo print_r($arr);
            echo "</pre>";
        }
        $namakegiatan = $this->Bkm->bkmSetupKegiatan();


?>
        <fieldset style='min-height:100%;padding:30px;margin:0'>
            <table cellpadding=3 cellspacing=1 class=sortable>
                <tbody class=rowcontent>
                    <tr>
                        <td>Kode Organisasi</td>
                        <td> :</td>
                        <td><? echo $kodeorg ?></td>
                    </tr>
                    <tr>
                        <td>No BKM</td>
                        <td> :</td>
                        <td><b><? echo $notransaksi ?></b></td>
                    </tr>
                    <tr>
                        <td>No Transaksi</td>
                        <td> :</td>
                        <td><b><? echo $notransaksi ?></b></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Prestasi</p>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td align=center>No</td>
                        <td align=center>Tanggal</td>
                        <td align=center>Blok</td>
                        <td align=center>Status</td>
                        <td align=center>Nama Kegiatan</td>
                        <td align=center>Satuan</td>
                        <td align=center>Hasil Kerja</td>
                        <td align=center>Jumlah HK</td>
                        <td align=center>Photo</td>
                        <td align=center>Photo Akhir</td>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    $totphk = array_sum(array_column($dataPrestasi, 'hasilkerja'));
                    $totpjhk = array_sum(array_column($dataPrestasi, 'jumlahhk'));
                    if (count($dataPrestasi) > 0) {
                        foreach ($dataPrestasi as $key => $value) {
                            $nomor++;
                            $stsBlk = $this->M_rawat->getStatusBlok("where kodekegiatan = '{$value['kodekegiatan']}'");
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $nomor ?></td>
                                <td align=center><? echo $value['tanggal'] ?></td>
                                <td align=center><? echo $value['kodeorg'] ?></td>
                                <td align=center><? echo $stsBlk[0]['kelompok'] ?? "" ?></td>
                                <td align=left><strong><? echo isset($namakegiatan[$value['kodekegiatan']]['namakegiatan']) ? $namakegiatan[$value['kodekegiatan']]['namakegiatan'] : 'undefined' ?></strong><? echo ' (' . $value['kodekegiatan'] . ')' ?></td>
                                <td align=center><? echo isset($namakegiatan[$value['kodekegiatan']]['satuan']) ? $namakegiatan[$value['kodekegiatan']]['satuan'] : 'undefined' ?></td>
                                <td align=center><? echo $value['hasilkerja'] ?></td>
                                <td align=center><? echo $value['jumlahhk'] ?></td>
                                <td align=center><img title='Foto' class='resiconn' style='width:20px;height:20px;' src=<? echo $value['photo'] ?>></td>
                                </td>
                                <td align=center><img title='Foto' class='resiconn' style='width:20px;height:20px;' src=<? echo $value['photo2'] ?>></td>
                                </td>

                            </tr>
                        <? }
                        ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td colspan="6" align=center><? echo 'Total' ?></td>
                            <td align=center><? echo $totphk ?></td>
                            <td align=center><? echo $totpjhk ?></td>
                            <td colspan="2" align=center></td>
                        </tr>
                        <?
                        ?>
                    <? } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td colspan=10 style="text-align: center;">Data Tidak Ditemukan</td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
            </br>
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Kehadiran</p>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td align=center>No</td>
                        <td align=center>Kegiatan</td>
                        <td align=center>Blok</td>
                        <td align=center>Nama Karyawan</td>
                        <td align=center>Kehadiran</td>
                        <td align=center>Hasil Kerja</td>
                        <td align=center>Jumlah HK</td>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    $totkhk = array_sum(array_column($dataKehadiran, 'hasilkerja'));
                    $totkjhk = array_sum(array_column($dataKehadiran, 'jumlahhk'));
                    if (count($dataKehadiran) > 0) {
                        foreach ($dataKehadiran as $key => $value) {

                            $nomor++;
                            $namakeg = $this->Bkm->getNamaKegiatan("where kodekegiatan='{$value['kodekegiatan']}'");
                            $karyawanNM = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid='{$value['nik']}'");
                            // pre_arr($value)
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $nomor ?></td>
                                <td align=left><strong><? echo  $namakeg ?></strong></td>
                                <!-- <td align=center> -->
                                <!-- < echo $value['notransaksi'] ?> -->
                                <!-- </td> -->
                                <td align=center><? echo $value['kodeorg'] ?></td>
                                <td align=left><? echo $karyawanNM ?></td>
                                <!-- <td align=center> -->
                                <!-- <strong> -->
                                <!-- < echo isset($namakegiatan[$value['kodekegiatan']]['namakegiatan']) ? $namakegiatan[$value['kodekegiatan']]['namakegiatan'] : 'undefined' ?> -->
                                <!-- </strong> -->
                                <!-- < echo ' (' . $value['kodekegiatan'] . ')' ?> -->
                                <!-- </td> -->
                                <td align=center><? echo 'H' ?></td>
                                <!-- <td align=center> -->
                                <!-- < -->
                                <!-- echo isset($namakegiatan[$value['kodekegiatan']]['satuan']) ? $namakegiatan[$value['kodekegiatan']]['satuan'] : 'undefined'  -->
                                <!-- > -->
                                <!-- </td> -->
                                <td align=center><? echo $value['hasilkerja'] ?></td>
                                <td align=center><? echo $value['jumlahhk'] ?></td>
                            </tr>
                        <? }
                        ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td colspan="5" align=center><? echo 'Total' ?></td>
                            <td align=center><? echo $totkhk ?></td>
                            <td align=center><? echo $totkjhk ?></td>
                        </tr>
                        <?
                        ?>
                    <? } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td colspan=9 style="text-align: center;">Data Tidak Ditemukan</td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
            </br>
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Material</p>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td align=center>No</td>
                        <td align=center>Kegiatan</td>
                        <td align=center>Blok</td>
                        <td align=center>Nama Barang</td>
                        <td align=center>Satuan</td>
                        <td align=center>Kuantitas</td>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    if (count($getMaterial) > 0) {
                        foreach ($getMaterial as $key => $value) {
                            $nomor++;
                            $matDtl = $this->Bkm->getMaterialDtl("where kodebarang = '{$value['kodebarang']}'");
                        

                            $kegNm = $this->Bkm->getNamaKegiatan("where kodekegiatan = '{$value['kodekegiatan']}'");
                            // pre_arr($value)
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $nomor ?></td>
                                <td align=left><? echo $kegNm ?></td>
                                <td align=center><? echo $value['kodeorg'] ?></td>
                                <td align=left><? echo $matDtl[0]['namabarang'] ?></td>
                                <td align=center><? echo $matDtl[0]['satuan'] ?></td>
                                <td align=center><? echo $value['kwantitas'] ?></td>
                            </tr>
                        <? } ?>
                    <? } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td colspan=9 style="text-align: center;">Data Tidak Ditemukan</td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
        </fieldset>
    <?
    }

    function getTable($data)
    {
        // No.	No Transaksi	No. Referensi	Unit	Kemandoran / Vendor	Tanggal	Jjg	Mandor	Mandor 1	Asisten	Approval	Kerani Panen	Last Update	Tanggal Input	Tanggal Stages	Status Stages	Posting By	Tanggal Posting	Aksi
        $thead =  "<thead>
                    <tr class=\"rowheader\">
                        <th align=\"center\">No</th>
                        <th align=\"left\">No. Transaksi</th>
                        <th align=\"center\">No. Referensi</th>
                        <th align=\"center\">Organisasi</th>
                        <th align=\"center\">Hari</th>	
                        <th align=\"center\">Tanggal</th>
                        <th align=\"center\">Mandor</th>
                        <th align=\"center\">Mandor 1</th>
                        <th align=\"center\">Kerani</th>
                        <th align=\"center\">Asisten</th>
                        <th align=\"center\">Last Update</th>
                        <th align=\"center\">Pembuat</th>
                    </tr>
                </thead>";
        $datakar = $this->Setup_datakaryawan->selectOpt();
        $n = 1;
        $dataTable = "";
        if ($data->rowCount() > 0) {

            //foreach($data as $k=>$v){
            while ($v = $data->fetch()) {
                $act = array();
                $act['view'] = "listAction('?switcher=view&notransaksi={$v->notransaksi}&orgcd={$v->kodeorg}');";
                if ($this->priv->imAdmin()) {
                    if ($v->jurnal == 1) {
                        $act['unposting'] = "listAction('?switcher=unposting&id={$v->notransaksi}');"; //unposting
                    } else {
                        $act['delete'] = "callback=deleteActionHeader('" . $v->notransaksi . "');"; //delete
                        $act['posting'] = "callback=postingBkm('" . $v->notransaksi . "');"; //posting
                        $act['update'] = "listAction('?switcher=update&id={$v->notransaksi}');"; //update
                    }
                }
                $action = $this->toAtrr($act);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">{$n}</td>";
                $dataTable .= "<td align=\"left\">{$v->notransaksi}</td>";
                $dataTable .= "<td align=\"left\">{$v->noreferensi}</td>";
                $dataTable .= "<td align=\"center\">{$v->kodeorg}</td>";
                $dataTable .= "<td align=\"center\">" . $this->M_rawat->namaHari($v->updatetime) . "</td>";
                $dataTable .= "<td align=\"center\">{$v->tanggal}</td>";
                $dataTable .= "<td align=\"center\">{$datakar[$v->nikmandor]}</td>";
                $dataTable .= "<td align=\"center\">" . @$datakar[$v->nikmandor1] . "</td>";
                $dataTable .= "<td align=\"center\">" . @$datakar[$v->kerani] . "</td>";
                $dataTable .= "<td align=\"center\">" . @$datakar[$v->nikasisten] . "</td>";
                $dataTable .= "<td align=\"center\">" . date("d F Y", strtotime($v->updatetime)) . "</td>";
                $dataTable .= "<td align=\"center\">{$datakar[$v->createby]}</td>";
                $dataTable .= "</tr>";
                $n++;
            }
        } else {

            $dataTable = "<tr>";
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
                        <select classs="col-4" name="tanggal" onchange="" title="Tanggal" required="" search="true"></select>
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
        $option['getpage']      = 'switcher';
        $option['type']         = '';
        $option['javascript']['src'] = array($this->base_url() . 'js/' . $SELF . '.js?version=' . time() . '');

        $d = array();
        $d['title'] = "Form Entry Data";
        $d['slave'] = "form";
        $d['text'] = "new";
        $d['show'] = false;
        $d['isEnable'] = false;
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
        $d['show'] = false;
        $d['isEnable'] = false;
        $option['filter'] = $d;

        $option['breadcrumb']['title'] = $breadcrumb;

        $option['excel']['show'] = false;
        $option['pdf']['show'] = false;
        $option['csv']['show'] = false;
        $option['fixHeader']['show'] = false;
        $option['actions'] = array();
        $option['pathinfo']['site_url'] = $this->site_url();
        $option['pathinfo']['base_url'] = $this->base_url();
        $OPT =  json_encode($option);
        return $OPT;
    }
}
?>