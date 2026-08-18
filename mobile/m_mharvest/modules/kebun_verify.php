<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_verify extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_datakaryawan');
        $this->load->model('K_verify');
        $this->load->model('Privilege', 'priv');
    }

    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'pdf':
                $this->load->lib('Mpdf', 'pdf');
                $mpdf = $this->lib->pdf->create();
            case 'excel':
            case 'csv':
            default:
                // $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
                $page = (int)$this->get('page');
                ////////////////////////////
                //Pengambilan Data Total Row
                $getData = $this->K_verify->selectQuery();


                // $getData = $this->Mpanen->aktifitas($dataWhere);
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
                $dataTable = $this->getTable($this->K_verify->selectQuery([$tab->cur_page, $tab->per_page]));
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


                // if ($getData and $getData->rowCount() > 0) {
                //     //Pagination setup and load
                //     $this->load->lib("Pagination", "paging");
                //     $tab = $this->lib->paging;
                //     // $tab->id = "Panen";
                //     $tab->total_rows = $getData->rowCount();
                //     //row has definition
                //     $tab->per_page = 30; //LIMIT : default 20
                //     $tab->cur_page = $page;
                //     $starting_limit = ($page - 1) * $tab->per_page;
                //     //get Data per page == 
                //     $dataTable = $this->getTable($this->K_verify->selectQuery([$tab->cur_page, $tab->per_page]));
                //     //create HTML to json
                //     $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                //     $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                //     //Build HTML
                //     $tab->build();
                //     if ($case == 'pdf') {
                //         $mpdf->WriteHTML($tab->getHTML()->forPDF);
                //         $mpdf->Output();
                //     } else {
                //         //Load HTML 
                //         $tab->loadHTML();
                //         //Process End 
                //     }
                //     //Load HTML  
                //     $tab->loadHTML();
                //     //Process End
                // }
                break;
            case 'form':
                // $this->formFormat();
                break;
            case 'view':
                $notxn = $this->get('id');
                $tgl = $this->get('dt');
                $kodeOrg = $this->get('kodeorg');
                $header = array(
                    'txn' => $notxn,
                    'date' => $tgl,
                    'kodeorg' => $kodeOrg
                );
                // $getMutu = $this->K_verify->getMutu('where notransaksi="' . $notxn . '"');
                $dataDtl = $this->K_verify->getDataDtl($notxn);
                $kodemutu = $this->K_verify->kodeMutu();
                // $getPrestasi = $this->K_verify->getPrestasi('where notransaksi="' . $notxn . '"');
                $this->getView($header, $kodemutu, $dataDtl);
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
        $kmLen = count($mutu);
?>
        <style>
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
                        <td><b><? echo $header['kodeorg'] ?></b></td>
                    </tr>
                    <tr>
                        <td>No Transaksi</td>
                        <td> :</td>
                        <td><b><? echo $header['txn'] ?></b></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td> :</td>
                        <td><b><? echo $header['date'] ?></b></td>
                    </tr>
                </tbody>
            </table>
            </br>
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
                    </tr>
                    <tr>
                        <td align=center>Jjg</td>
                        <td align=center>Jjg AI</td>
                        <td align=center>Brondolan</td>
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
                        foreach ($dtl as $key => $value) {
                            $nomor++;
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
                                foreach ($mutu as $key => $value) {
                                    $qtyVerif = $this->Mpanen->getMutuQty($whrQtyVerif . " and kodedenda = '{$value['kode']}'");
                                    $mhValVerif++;
                                ?>
                                    <td align=center style="width:5%"><input type="number" onchange="onchangeinput('<?= $nomor ?>', '<?= $kmLen ?>')" onwheel="return false;" id="inputDtlrwverif<?= $nomor . $mhValVerif ?>" maxlength="2" style="width:100%" value="<?= $qtyVerif ?>" disabled></td>
                                <?
                                }
                                ?>
                            </tr>
                        <? } ?>
                    <? } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <?
                            $totRw = count($mutu) + 13;
                            ?>
                            <td colspan=<?= $totRw - 1 ?> style="text-align: center;">Data Tidak Ditemukan</td>
                            <?
                            ?>
                        </tr>
                    <? }
                    ?>
                </tbody>
            </table>
        </fieldset>
<?
    }

    function getTable($data)
    {
        // No.	No Transaksi	No. Referensi	Unit	Kemandoran / Vendor	Tanggal	Jjg	Mandor	Mandor 1	Asisten	Approval	Kerani Panen	Last Update	Tanggal Input	Tanggal Stages	Status Stages	Posting By	Tanggal Posting	Aksi
        // echo($this->priv->imAdmin());
        $thead =  "<thead>
                    <tr class=\"rowheader\">
                        <th align=\"center\">No</th>
                        <th align=\"left\">Transaksi</th>
                        <th align=\"center\">Kode Org</th>
                        <th align=\"center\">Divisi</th>
                        <th align=\"center\">Tanggal</th>
                        <th align=\"center\">NIK Mandor</th>	
                        <th align=\"center\">Last Update</th>
                    </tr>
                </thead>";
        // $datakar = $this->Setup_datakaryawan->selectOpt();
        $n = 1;
        $dataTable = "";
        if ($data->rowCount() > 0) {
            // exit($this->priv->imAdmin());
            while ($v = $data->fetch()) {
                $act = array();
                // print_r($v);
                $karyawanNM = $this->Setup_datakaryawan->selectPemanenNm("where karyawanid='{$v['nikmandor']}'");

                $act['view'] = "listAction('?switcher=view&id={$v['notransaksi']}&dt={$v['tanggal']}&kodeorg={$v['kodeorg']}');";
                // $act['unposting'] = "listAction('?switcher=unposting&id={$v['notransaksi']}');"; //unposting
                if ($this->priv->imAdmin()) {
                    if ($v['syn'] == 1) {
                        // $act['unposting'] = "listAction('?switcher=unposting&id={$v['notransaksi']}');"; //unposting
                    } else {
                        // $act['delete'] = "callback=deleteMutuhancak('" . $v['notransaksi'] . "');"; //delete
                        // $act['posting'] = "callback=postingMutuhancak('" . $v['notransaksi'] . "');"; //posting
                        // $act['update'] = "listAction('?switcher=update&id={$v['notransaksi']}');"; //posting
                    }
                }
                $action = $this->toAtrr($act);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">{$n}</td>";;
                $dataTable .= "<td align=\"left\">{$v['notransaksi']}</td>";
                $dataTable .= "<td align=\"left\">{$v['kodeorg']}</td>";
                $dataTable .= "<td align=\"left\">{$v['divisi']}</td>";
                $dataTable .= "<td align=\"left\">{$v['tanggal']}</td>";
                $dataTable .= "<td align=\"left\">{$karyawanNM}</td>";
                $dataTable .= "<td align=\"left\">{$v['updatetime']}</td>";
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