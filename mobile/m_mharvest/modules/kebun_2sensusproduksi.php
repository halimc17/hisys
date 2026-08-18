<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_2sensusproduksi extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Sensusproduksi');
        $this->load->model('Setup_datakaryawan');
        $this->load->model('Setup_arah');
        $this->load->model('Privilege', 'priv');
    }

    // function deleteTxn($notransaksi)
    // {
    //     $whr = "notransaksi='" . $notransaksi . "'";
    //     echo $whr . ' ' . $notransaksi;

    //     $this->Mhancak->deleteMutuhancak($whr);
    // }

    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'pdf':
            case 'excel':
            case 'csv':
            default:
                // $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
                $page = (int)$this->get('page');
                ////////////////////////////
                //Pengambilan Data Total Row
                $getData = $this->Sensusproduksi->selectQuery();
                if ($getData and $getData->rowCount() > 0) {
                    //Pagination setup and load
                    $this->load->lib("Pagination", "paging");
                    $tab = $this->lib->paging;
                    // $tab->id = "Panen";
                    $tab->total_rows = $getData->rowCount();
                    //row has definition
                    $tab->per_page = 30; //LIMIT : default 20
                    $tab->cur_page = $page;
                    $starting_limit = ($page - 1) * $tab->per_page;
                    //get Data per page == 
                    $dataTable = $this->getTable($this->Sensusproduksi->selectQuery([$tab->cur_page, $tab->per_page]));
                    //create HTML to json
                    $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                    $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                    //Build HTML
                    $tab->build();
                    //Load HTML  
                    $tab->loadHTML();
                    //Process End
                }
                break;
            case 'form':
                // $this->formFormat();
                break;
            case 'view':
                $notransaksi = $this->get('id');
                $divisi = $this->get('divisi');
                $date = $this->get('date');
                $header = array(
                    'txn' => $notransaksi,
                    'divisi' => $divisi,
                    'dt' => $date
                );
                $mutu = $this->Sensusproduksi->kodeMutu();
                $dataDtl = $this->Sensusproduksi->getDataDtl("where notransaksi = '$notransaksi'");
                $this->getView($header, $mutu, $dataDtl);
                break;
            case 'Filter':
                break;
            case 'insert':
                break;
            case 'delete':
                // $notransaksi = $this->get('notransaksi');
                // $this->deleteTxn($notransaksi);
                break;
            case 'posting':
                // $notransaksi = $this->get('notransaksi');
                // $this->postingTransaksi($notransaksi);
                break;
        }
    }

    function getView($header, $mutu, $dtl)
    {
?>
        <fieldset style='min-height:100%;padding:30px;margin:0'>
            <table cellpadding=3 cellspacing=1 class=sortable>
                <tbody class=rowcontent>
                    <tr>
                        <td>Divisi</td>
                        <td> :</td>
                        <td><b><? echo $header['divisi'] ?></b></td>
                    </tr>
                    <tr>
                        <td>No Transaksi</td>
                        <td> :</td>
                        <td><b><? echo $header['txn'] ?></b></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td> :</td>
                        <td><b><? echo $header['dt'] ?></b></td>
                    </tr>
                </tbody>
            </table>
            <br />
            </br>
            <!-- <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">PRESTASI</p> -->
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td rowspan="2" align=center>No</td>
                        <td rowspan="2" align=center>No Transaksi</td>
                        <td rowspan="2" align=center>Blok</td>
                        <td rowspan="2" align=center>Nama Karyawan</td>
                        <td rowspan="2" align=center>Arah</td>
                        <td rowspan="2" align=center>Tanggal</td>
                        <td rowspan="2" align=center>Baris</td>
                        <td rowspan="2" align=center>Pokok</td>
                        <?
                        foreach ($mutu as $key => $value) {
                        ?>
                            <td align=center style="width:5%"><? echo $value['kriteria'] ?></td>
                        <?
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    if (count($dtl) > 0) {
                        $arah = $this->Setup_arah->getArah();
                        foreach ($dtl as $key => $value) {
                            $nomor++;
                            $nm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '{$value['header']['nik']}'");
                            $whrQty = "where notransaksi = '{$value['header']['txn']}' and arahsensus = '{$value['header']['arah']}'";
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td rowspan="<?= count($value['detail']) + 1 ?>" style="vertical-align:middle" border="1">
                                    <center><? echo $nomor ?></center>
                                </td>
                                <td rowspan="<?= count($value['detail']) + 1 ?>" style="vertical-align:middle" border="1">
                                    <center><? echo $value['header']['txn'] ?></center>
                                </td>
                                <td rowspan="<?= count($value['detail']) + 1 ?>" style="vertical-align:middle" border="1">
                                    <center><? echo $value['header']['blok'] ?></center>
                                </td>
                                <td rowspan="<?= count($value['detail']) + 1 ?>" style="vertical-align:middle" border="1">
                                    <center><? echo $nm . "({$value['header']['nik']})" ?></center>
                                </td>
                                <td rowspan="<?= count($value['detail']) + 1 ?>" style="vertical-align:middle" border="1">
                                    <center><? echo $arah[$value['header']['arah']] ?></center>
                                </td>
                                <td rowspan="<?= count($value['detail']) + 1 ?>" style="vertical-align:middle" border="1">
                                    <center><? echo $value['header']['tgl'] ?></center>
                                </td>
                                <?
                                foreach ($value['detail'] as $key => $val) {
                                    $raw = explode("-", $val['urut']);
                                    $baris = $raw[0];
                                    $pokok = $raw[1];
                                ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $baris ?></td>
                                <td align=center><? echo $pokok ?></td>
                                <?
                                    foreach ($mutu as $key => $mut) {
                                        $qty = 0;
                                        $qty = $this->Sensusproduksi->getMutuQty($whrQty . " and nourut = '{$val['urut']}' and idjenis = '{$mut['idjenis']}'");
                                ?>
                                    <td align=center style="width:5%"><? echo $qty ?></td>
                                <?
                                    }
                                ?>
                            </tr>
                        <?
                                }
                        ?>
                        </tr>
                    <? } ?>
                <? } else { ?>
                    <tr class=rowcontent style=vertical-align:top;>
                        <td colspan=9 style="text-align: center;">Data Tidak Ditemukan</td>
                    </tr>
                <? }
                ?>
                </tbody>
            </table>
        </fieldset>
    <?
    }

    function postingData()
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
        $thead = "hello";
        $dataTable = "data";
        $result['head'] = $thead;
        $result['body'] = $dataTable;
        return (object)$result;
        // $this->Mhancak->posting($v);
    }

    function getTable($data)
    {

        $thead =  "<thead>
                    <tr class=\"rowheader\">
                        <th align=\"center\">No</th>
                        <th align=\"left\">Tanggal</th>
                        <th align=\"left\">No Transaksi</th>
                        <th align=\"center\">Divisi</th>
                        <th align=\"center\">Last Update</th>	
                    </tr>
                </thead>";
        $n = 1;
        $dataTable = "";
        if ($data->rowCount() > 0) {
            // exit($this->priv->imAdmin());
            while ($v = $data->fetch()) {
                $act = array();
                $act['view'] = "listAction('?switcher=view&id={$v['notransaksi']}&date={$v['tanggal']}&divisi={$v['divisi']}');";
                // $act['unposting'] = "listAction('?switcher=unposting&id={$v['notransaksi']}');"; //unposting
                if ($this->priv->imAdmin()) {
                    if ($v['jurnal'] == 1) {
                        // $act['unposting'] = "listAction('?switcher=unposting&id={$v['notransaksi']}');"; //unposting
                    } else {
                        // $act['delete'] = "callback=deleteMutuhancak('" . $v['notransaksi'] . "');"; //delete
                        // $act['posting'] = "callback=postingMutuhancak('" . $v['notransaksi'] . "');"; //posting
                        // $act['update'] = "listAction('?switcher=update&id={$v['notransaksi']}');"; //posting
                    }
                }
                ($v['updateby'] == null || $v['updateby'] == '') ? $nm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '{$v['createby']}'") : $nm = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid = '{$v['updateby']}'");
                $action = $this->toAtrr($act);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">{$n}</td>";;
                $dataTable .= "<td align=\"left\">{$v['tanggal']}</td>";
                $dataTable .= "<td align=\"left\">{$v['notransaksi']}</td>";
                $dataTable .= "<td align=\"left\">{$v['divisi']}</td>";
                $dataTable .= "<td align=\"left\">{$nm}</td>";
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