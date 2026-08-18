<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_luaspanen extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_datakaryawan');
        $this->load->model('Mpanen');
        $this->load->model('Mluaspanen');
        $this->load->model('Privilege', 'priv');
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
                $page = (int)$this->get('page');

                ////////////////////////////
                // $getData = $this->Mpanen->aktifitas($dataWhere);
                $whr = 'WHERE kodeorg="' . $_SESSION['empl']['lokasitugas'] . '" ORDER BY tanggal DESC ';
                $getData = $this->Mluaspanen->getHeader($whr);

                if ($getData and $getData->rowCount() > 0) {
                    $this->load->lib("Pagination", "paging");
                    $tab = $this->lib->paging;
                    $tab->id = "luaspanen";
                    $tab->total_rows = $getData->rowCount();

                    //row has definition
                    $tab->per_page = 30; //LIMIT : default 20
                    $tab->cur_page = $page;
                    $starting_limit = ($page - 1) * $tab->per_page;
                    //get Data per page == 
                    $dataTable = $this->getTable($this->Mluaspanen->getHeader($whr, [$tab->cur_page, $tab->per_page]));
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
                }

                break;
            case 'form':
                $this->formFormat();
                break;
            case 'view':
                $notxn = $this->get('id');
                $tgl = $this->get('tgl');
                $unit = $this->get('kodeorg');
                $data = $this->Mluaspanen->haPanenDtlView("where notransaksi = '$notxn'");
                $this->getView($notxn, $tgl, $unit, $data);
                break;
            case 'Filter':
                $this->filter_panen();
                break;
            case 'insert':
                break;
        }
    }
    function getView($id, $dt, $unit, $data)
    {
?>
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
                        <td><b><? echo $dt ?></b></td>
                    </tr>
                </tbody>
            </table>
            <br />
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Rekap</p>
            </br>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td rowspan="2" align=center>No</td>
                        <td rowspan="2" align=center>Blok</td>
                        <td rowspan="2" align=center>Nama</td>
                        <td rowspan="2" align=center>Luas Rencana</td>
                        <td rowspan="2" align=center>Luas Aktual</td>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            $nomor++;
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo  $nomor ?></td>
                                <td align=center><? echo $value['blok'] ?></td>
                                <td align=center><? echo $value['nama'] ?></td>
                                <td align=center><? echo $value['luas_rencana'] ?></td>
                                <td align=center><? echo $value['luas_aktual'] ?></td>
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
                        <th align=\"center\">Tanggal Panen</th>
                        <th align=\"center\">Unit</th>
                        <th align=\"center\">Mandor</th>	
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
                $act['view'] = "listAction('?switcher=view&id={$v->notransaksi}&tgl={$v->tanggalpanen}&kodeorg={$v->divisi}');";
                // if ($this->priv->imAdmin()) {
                // if ($v->flag == 1) {
                // $act['unposting'] = "listAction('?switcher=unposting&id={$v->notransaksi}');";//unposting
                // } else {
                // $act['delete'] = "listAction('?switcher=delete&id={$v->notransaksi}');";//delete
                // $act['posting'] = "listAction('?switcher=posting&id={$v->notransaksi}');";//posting
                // $act['update'] = "listAction('?switcher=update&id={$v->notransaksi}');";//posting
                // }
                // }
                $action = $this->toAtrr($act);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">" . $n . "</td>";
                $dataTable .= "<td align=\"left\">" . $v->notransaksi . "</td>";
                $dataTable .= "<td align=\"center\">" . date("d F Y", strtotime($v->tanggalpanen)) . "</td>";
                $dataTable .= "<td align=\"center\">" . $v->kodeorg . "</td>";
                $dataTable .= "<td align=\"center\">" . @$datakar[$v->mandor] . "</td>";
                $dataTable .= "<td align=\"center\">" . date("d F Y", strtotime($v->updatetime)) . "</td>";
                $dataTable .= "<td align=\"center\">{$datakar[$v->createby]}</td>";
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