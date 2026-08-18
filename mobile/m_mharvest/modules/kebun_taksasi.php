<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_taksasi extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mtaksasi');
        $this->load->model('Setup_datakaryawan');
        $this->load->model('Blok');
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
                $getData = $this->Mtaksasi->selectQuery();
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
                    $dataTable = $this->getTable($this->Mtaksasi->selectQuery([$tab->cur_page, $tab->per_page]));
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
                $orgcd = substr($divisi, 0, 4);
                $header = array(
                    'txn' => $notransaksi,
                    'divisi' => $divisi,
                    'dt' => $date
                );
                $dataDtl = $this->Mtaksasi->getDataDtl("where tanggal = '$date' and kodeorg = '$orgcd' and divisi = '$divisi' and tipetransaksi = 'TKS'");
                $this->getView($header, $dataDtl);
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

    function getView($header, $dtl)
    {
        $isShown = false;
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
                        <td rowspan="2" align=center>Divisi</td>
                        <td rowspan="2" align=center>Nama Karyawan</td>
                        <td rowspan="2" align=center>Kavfeld</td>
                        <td rowspan="2" align=center>Blok</td>
                        <td rowspan="2" align=center>Luas Blok</td>
                        <td rowspan="2" align=center>Luas Panen</td>
                        <td rowspan="2" align=center>SPH</td>
                        <td rowspan="2" align=center>AKP</td>
                        <td rowspan="2" align=center>Jjg</td>
                        <td rowspan="2" align=center>Bjr Sample</td>
                        <td rowspan="2" align=center>Produksi</td>
                        <td rowspan="2" align=center>HK</td>
                        <td rowspan="2" align=center>Output</td>
                        <td rowspan="2" align=center>Daya Jelajah</td>
                    </tr>
                </thead>
                <tbody>
                    <?
                    if (count($dtl) > 0) {
                        $gtluasblok = 0.00;
                        $gtluaspanen = 0.00;
                        $gtjjg = 0.00;
                        $gtproduksi = 0.00;
                        $gthk = 0.00;
                        $gtoutput = 0.00;
                        $gtdaya = 0.00;
                        foreach ($dtl as $k => $v) {
                            $gtluasblok = $gtluasblok + $v['luasblok'];
                            $gtluaspanen = $gtluaspanen + $v['luaspanen'];
                            $gtjjg = $gtjjg + $v['totjjg'];
                            $gtproduksi = $gtproduksi + $v['produksi'];
                            $gthk = $gthk + $v['tothk'];

                            $gtoutput = number_format(((float)$gtproduksi / (float)$gthk), 2, ',', '');
                            $gtdaya = number_format(((float)$gtluaspanen / (float)$gthk), 2, ',', '');

                            for ($i = 0; $i < count($v['data']); $i++) {
                    ?>
                                <tr class=rowcontent style=vertical-align:top;>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo "DIVISI " . $v['data'][$i]['divisi'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['namakaryawan'] ?? "" ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo "" ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['blok'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['luasblok'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['luaspanen'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['sph'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['akp'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['jjg'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['bjr'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['produksi'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['tk_panen'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['output'] ?></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><? echo $v['data'][$i]['daya'] ?></center>
                                    </td>
                                </tr>
                            <?
                            }
                            if (count($v['data']) > 0) {
                                $isShown = true;
                            ?>
                                <tr class=rowcontent style=vertical-align:top;>
                                    <td colspan="4" style="vertical-align:middle" border="1">
                                        <center><b><? echo "Sub Total" ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo $v['luasblok'] ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo $v['luaspanen'] ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo "" ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo "" ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo $v['totjjg'] ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo "" ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo $v['produksi'] ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo $v['tothk'] ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo $v['totoutput'] ?></b></center>
                                    </td>
                                    <td style="vertical-align:middle" border="1">
                                        <center><b><? echo $v['dayajelajah'] ?></b></center>
                                    </td>

                                </tr>
                            <?
                            }
                        }
                        if ($isShown) {
                            ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td colspan="4" style="vertical-align:middle" border="1">
                                    <center><b><? echo "Grand Total Divisi " . $dtl[1]['data'][1]['divisi']  ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo $gtluasblok ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo $gtluaspanen ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo "" ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo "" ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo $gtjjg ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo "" ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo $gtproduksi ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo $gthk ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo $gtoutput ?></b></center>
                                </td>
                                <td style="vertical-align:middle" border="1">
                                    <center><b><? echo $gtdaya ?></b></center>
                                </td>

                            </tr>
                        <?
                        } else { ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <?
                                $totRw = 14;
                                ?>
                                <td colspan=<?= $totRw ?> style="text-align: center;">Data Tidak Ditemukan</td>
                            </tr>
                        <? }
                        ?>
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
                        <th align=\"center\">Tanggal</th>
                        <th align=\"center\">Perusahaan</th>
                        <th align=\"center\">Unit</th>
                        <th align=\"center\">Divisi</th>
                        <th align=\"center\">Last Update</th>	
                        <th align=\"center\">Last Update</th>	
                        <th align=\"center\">Posting By</th>	
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
                $dataTable .= "<td align=\"left\">{$v['kodeorg']}</td>";
                $dataTable .= "<td align=\"left\">{$v['kodeorg']}</td>";
                $dataTable .= "<td align=\"left\">{$v['divisi']}</td>";
                $dataTable .= "<td align=\"left\">{$v['updateby']}</td>";
                $dataTable .= "<td align=\"left\">{$v['updatetime']}</td>";
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