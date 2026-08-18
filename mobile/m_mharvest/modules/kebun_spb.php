<?

defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_spb extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_datakaryawan');
        $this->load->model('M_rawat');
        $this->load->model('Mspb');
        $this->load->model('Mtujuanspb');
        $this->load->model('Privilege', 'priv');
    }

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
                // $whr = 'WHERE kodeorg="' . $_SESSION['empl']['lokasitugas'] . '" ';
                // $getData = $this->M_rawat->getAktifitas($whr);
                // $getData = $this->Mspb->getHeaderSPB($whr);

                if (!empty($this->get('tanggal1'))) {
                    $filter['tanggal'] = date('Y-m-d', strtotime($this->get('tanggal1')));
                }
                if (!empty($this->get('periode'))) {
                    $filter['periode'] = $this->get('periode');
                }

                // $filter['kodeorg']=$_SESSION['empl']['lokasitugas'];
                // if (!empty($this->get('kodeorg'))) {
                //     $filter['kodeorg']=$this->get('kodeorg');
                // }
                // $filter['kodeorg'] = !empty($this->get('kodeorg')) ?: 'SD3E';
                $filter['kodeorg'] = $this->get('kodeorg') ?: $_SESSION['empl']['lokasitugas'];

                $getData = $this->Mspb->getdspb_sync($filter);
                // if ($getData and $getData->rowCount() > 0) {
                if ($getData && count($getData) > 0) {

                    //Pagination setup and load
                    $this->load->lib("Pagination", "paging");
                    $tab = $this->lib->paging;
                    // $tab->id = "Bkm";
                    // $tab->total_rows = $getData->rowCount();
                    $tab->total_rows = count($getData);
                    //row has definition
                    $tab->per_page = 30; //LIMIT : default 20
                    $tab->cur_page = $page;
                    $starting_limit = ($page - 1) * $tab->per_page;
                    // get Data per page == 

                    // $dataTable = $this->getTable($this->Mspb->getHeaderSPB($whr, [$tab->cur_page, $tab->per_page]));

                    $dataHeader = $this->Mspb->getdspb_sync($filter);
                    usort($dataHeader, function ($a, $b) {
                        return strtotime($b['tanggal']) - strtotime($a['tanggal']);
                    });

                    $dataTable = $this->getTable($dataHeader);


                    // $dataTable = $this->getTable($this->Mspb->getdspb_sync());
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
                $this->formFormat();
                break;
            case 'view':
                $detailSPB = $this->Mspb->getspbdetail_sync($this->get('id'));


                $this->getView($detailSPB);
                break;
            case 'Filter':
                $this->filter_spb();
                break;
            case 'insert':
                break;
        }
    }

    function getView($data)
    {
        $datakar = $this->Setup_datakaryawan->selectOpt();
        $namaSupir = $datakar[$data['kerani']] ?: $data['kerani'];
        $getTujuan = $this->Mtujuanspb->getTujuanSPB();

        function generateTree($data)
        {
            $html = '<ul><li>';
            $html .= '<div class="card">';
            $html .= '<img src="' . $data['ffbdocument'] . '" alt="Image">';
            $html .= '<a href="#">' . $data['nospb'] . '</a>';
            $html .= '</div>';

            if (!empty($data['child'])) {
                foreach ($data['child'] as $child) {
                    $html .= generateTree($child);
                }
            }

            $html .= '</li></ul>';
            return $html;
        }
?>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f4f4f4;
            }

            .tree {
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 20px;
            }

            .tree ul {
                position: relative;
                padding: 1em 0;
                white-space: nowrap;
                margin: 0 auto;
                text-align: center;
            }

            .tree ul::after {
                content: '';
                display: table;
                clear: both;
            }

            .tree li {
                display: inline-block;
                vertical-align: top;
                text-align: center;
                list-style-type: none;
                position: relative;
                padding: 1em .5em 0 .5em;
            }

            .tree li::before,
            .tree li::after {
                content: '';
                position: absolute;
                top: 0;
                right: 50%;
                border-top: 1px solid #ccc;
                width: 50%;
                height: 20px;
            }

            .tree li::after {
                right: auto;
                left: 50%;
                border-left: 2px solid #ccc;
            }

            .tree li:only-child::after,
            .tree li:only-child::before {
                display: none;
            }

            .tree li:only-child {
                padding-top: 0;
            }

            .tree li:first-child::before,
            .tree li:last-child::after {
                border: 0 none;
            }

            .tree li:last-child::before {
                border-right: 1px solid #ccc;
                border-radius: 0 5px 0 0;
            }

            .tree li:first-child::after {
                border-radius: 5px 0 0 0;
            }

            .tree ul ul::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                border-left: 2px solid #ccc;
                width: 0;
                height: 20px;
            }

            .tree li a {
                border: 1px solid #ccc;
                padding: .5em 1em;
                text-decoration: none;
                color: #666;
                background-color: #fff;
                display: inline-block;
                border-radius: 5px;
                transition: all 0.5s;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .tree li a:hover,
            .tree li a:hover+ul li a {
                background: #e9453f;
                color: #fff;
                border: 1px solid #e9453f;
            }

            .tree li a:hover+ul li::after,
            .tree li a:hover+ul li::before,
            .tree li a:hover+ul::before,
            .tree li a:hover+ul ul::before {
                border-color: #e9453f;
            }

            .tree ul ul::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                border-left: 2px solid #ccc;
                width: 0;
                height: 20px;
            }

            .tree li .card {
                border: 2px solid #ccc;
                padding: 10px;
                border-radius: 5px;
                background: #fff;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
                display: inline-block;
                text-align: center;
                transition: transform 0.3s;
            }

            .tree li .card img {
                border-radius: 50%;
                width: 100px;
                height: 100px;
                transition: transform 0.3s;
            }

            .tree li .card:hover img {
                transform: scale(1.5);
            }

            .tree li a {
                text-decoration: none;
                color: #666;
                background: #dedede;
                font-size: 12px;
                display: block;
                margin-top: 10px;
                transition: all 0.5s;
            }

            .tree li a:hover,
            .tree li a:hover+ul li a {
                background: #c8e4f8;
                color: #000;
                border: 2px solid #94a0b4;
            }

            .tree li a:hover+ul li::after,
            .tree li a:hover+ul li::before,
            .tree li a:hover+ul::before,
            .tree li a:hover+ul ul::before {
                border-color: #94a0b4;
            }
        </style>

        <fieldset style='min-height:100%;padding:30px;margin:0'>
            <table cellpadding=3 cellspacing=1 class=sortable width=300px>
                <tbody class=rowcontent>
                    <tr>
                        <td>SPB No.</td>
                        <td>:</td>
                        <td><?= $data['nospb'] ?></td>
                    </tr>
                    <tr>
                        <td>Tanggal SPB</td>
                        <td>:</td>
                        <td><?= $this->M_rawat->namaHari($data['tanggal']) . ', ' . date("d M Y", strtotime($data['tanggal'])) ?></td>
                    </tr>
                    <tr>
                        <td>Kode Organisasi</td>
                        <td>:</td>
                        <td><?= $data['kodeorg'] ?></td>
                    </tr>
                    <tr>
                        <td>SPB No.</td>
                        <td>:</td>
                        <td><?= $data['nospb'] ?></td>
                    </tr>
                    <tr>
                        <td>Supir</td>
                        <td>:</td>
                        <td><?= $namaSupir ?></td>
                    </tr>
                    <tr>
                        <td>Tujuan</td>
                        <td>:</td>
                        <td><?= $data['penerimatbs'] ?></td>
                    </tr>
                    <tr>
                        <td>Status Tujuan</td>
                        <td>:</td>
                        <td><?= $getTujuan[$data['tujuan']]['nama'] ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="tree">
                <?= generateTree($data) ?>
            </div>

            <br />
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Rekap</p>
            </br>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td rowspan="2" align=center>No</td>
                        <td rowspan="2" align=center>No Transaksi Panen</td>
                        <td rowspan="2" align=center>Tanggal</td>
                        <td rowspan="2" align=center>Blok</td>
                        <td rowspan="2" align=center>TPH</td>
                        <td rowspan="2" align=center>Nama Karyawan</td>
                        <td rowspan="2" align=center>Janjang</td>
                        <td rowspan="2" align=center>Brondolan</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $nomor = 0;
                    $ttljjg = 0;
                    $ttlbrondolan = 0;

                    if (count($data['docket']) > 0) {
                        foreach ($data['docket'] as $key => $value) {
                            $nomor++;
                            $ttljjg += $value['jjg'];
                            $ttlbrondolan += $value['brondolan'];
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><?= $nomor ?></td>
                                <td align=center><?= $value['nopnnref'] ?></td>
                                <td align=center><?= $value['tanggalpanen'] ?></td>
                                <td align=center><?= $value['blok'] ?></td>
                                <td align=center><?= $value['tph'] ?></td>
                                <td align=center><?= $datakar[$value['nik']] ?></td>
                                <td align=center><?= $value['jjg'] ?></td>
                                <td align=center><?= $value['brondolan'] ?></td>
                            </tr>
                        <?php } ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td align=right colspan=6>Jumlah</td>
                            <td align=center><?= $ttljjg ?></td>
                            <td align=center><?= $ttlbrondolan ?></td>
                        </tr>
                    <?php } else { ?>
                        <tr class=rowcontent style=vertical-align:top;>
                            <td colspan=17 style="text-align: center;">Data Tidak Ditemukan</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </fieldset>

    <?php
    }


    function getTable($data)
    {
        // No.	No Transaksi	No. Referensi	Unit	Kemandoran / Vendor	Tanggal	Jjg	Mandor	Mandor 1	Asisten	Approval	Kerani Panen	Last Update	Tanggal Input	Tanggal Stages	Status Stages	Posting By	Tanggal Posting	Aksi
        $thead =  "<thead>
                    <tr align=\"center\" class=\"rowheader\">
                        <th>No</th>
                        <th>No. SPB</th>
                        <th>Status</th>
                        <th>Hari - Tanggal</th>
                        <th>Organisasi</th>
                        <th>Divisi</th>	
                        <th>Kerani Transport</th>
                        <th>No Polisi</th>
                        <th>Nama Supir</th>
                        <th>Last Update</th>
                        <th>Pembuat</th>
                    </tr>
                </thead>";
        $datakar = $this->Setup_datakaryawan->selectOpt();
        $n = 1;
        $dataTable = "";

        // if ($data->rowCount() > 0) {
        if (count($data) > 0) {
            //foreach($data as $k=>$v){
            // while ($v = $data->fetch()) {
            foreach ($data as $key => $v) {
                $action = array();
                $action['view'] = "listAction('?switcher=view&id={$v['nospb']}');";
                if ($this->priv->imAdmin()) {
                    if ($v['flag'] == 1) {
                        $action['unposting'] = "listAction('?switcher=unposting&id={$v['nospb']}');"; //unposting
                    } else {
                        $action['delete'] = "listAction('?switcher=delete&id={$v['nospb']}');"; //delete
                        $action['posting'] = "listAction('?switcher=posting&id={$v['nospb']}');"; //posting
                        $action['update'] = "listAction('?switcher=update&id={$v['nospb']}');"; //posting
                    }
                }

                // $tujuan = ['Internal', 'Afiliasi', 'TPB', 'External'];
                $getTujuan = $this->Mtujuanspb->getTujuanSPB();
                $namaSupir = $datakar[$v['kerani']] ?: $v['kerani'];

                $action = $this->toAtrr($action);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">{$n}</td>";
                $dataTable .= "<td align=\"left\">{$v['nospb']}</td>";
                // $dataTable .= "<td align=\"left\">{$tujuan[$v['tujuan']]}</td>";
                $dataTable .= "<td align=\"left\">{$getTujuan[$v['tujuan']]['nama']}</td>";
                $dataTable .= "<td align=\"center\">" . $this->M_rawat->namaHari($v['tanggal']) . ", {$v['tanggal']}</td>";
                $dataTable .= "<td align=\"center\">{$v['kodeorg']}</td>";
                $dataTable .= "<td align=\"center\">{$v['divisi']}</td>";
                $dataTable .= "<td align=\"center\">{$datakar[$v['kraniproduksi']]}</td>";
                $dataTable .= "<td align=\"center\">{$v['nopol']}</td>";
                $dataTable .= "<td align=\"center\">" . $namaSupir . "</td>";
                $dataTable .= "<td align=\"center\">" . date("d F Y H:i:s", strtotime($v['createtime'])) . "</td>";
                $dataTable .= "<td align=\"center\">{$datakar[$v['createby']]}</td>";
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
        $d['show'] = true;
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

    function filter_spb()
    {
        $whereOpt = "Where kodeorg = '" . $_SESSION['empl']['lokasitugas'] . "'  ";
        $spb = $this->Mspb->getHeaderSPB($whereOpt);
        $qUnit = $this->Mspb->getHeaderSPB('group by `kodeorg` order by `kodeorg`');
        $qdiv = $this->Mspb->getHeaderSPB($whereOpt . 'group by `divisi` order by `divisi`');
        $datares = [];

        foreach ($qUnit as $key => $value) {
            $datares['unit'][] = $value->kodeorg;
        }

        $optionsUnit = '';
        foreach ($datares['unit'] as $val) {
            $optionsUnit .= "<option value='$val'>$val</option>";
        }

        if (count($qdiv) > 0) {
            foreach ($qdiv as $key => $value) {
                $xe[$key] = $value;
                $datares['divisi'][] = $value->divisi;
            }
        }

        $optionsDiv = '';
        foreach ($datares['divisi'] as $val) {
            $optionsDiv .= "<option value='$val'>$val</option>";
        }

        $prd = [];
        foreach ($spb as $value) {
            $datetime = new DateTime($value->tanggal);
            $p = $datetime->format('Y-m');
            $prd[$p] = $p;
        }

        foreach ($prd as $key) {
            $datares['periode'][] = $key;
        }

        $optionsPeriode = '';
        foreach ($datares['periode'] as $p) {
            $optionsPeriode .= "<option value='$p'>$p</option>";
        }
    ?>

        <form method="GET">
            <div class="body-frame u-margin-10">
                <div class="row">
                    <div class="col-xl-8 col-md-8 col-sm-8 col-xs-12 u-margin-b-10" style="display:;">
                        <label>No Transaksi</label>
                        <input class="full-width" type="text" name="notransaksi" onchange="" value="<? echo $this->get('notransaksi'); ?>" title="No Transaksi" placeholder="00000000000000-00">
                    </div>
                    <div class="col-xl-8 col-md-8 col-sm-8 col-xs-12 u-margin-b-10"">
                        <label>Periode</label>
                        <select class=" full-width" id="periode" name="periode" onchange="updatetanggal(this.value); return false" title="Periode" search="false">
                        <?= $optionsPeriode; ?>
                        </select>
                    </div>
                    <div class="col-xl-8 col-md-8 col-sm-8 col-xs-12 u-margin-b-10">
                        <label>Tanggal</label>
                        <input class="full-width" id="tanggal" type="text" name="tanggal1" value="<? echo $this->get('tanggal1'); ?>" readonly="readonly" onchange="updatepriode(this.value);" onmousemove="setCalendar(this,'%d-%m-%Y');" autocomplete="off" title="Tanggal Mulai">
                    </div>
                    <div class="col-xl-8 col-md-8 col-sm-8 col-xs-12 u-margin-b-10">
                        <label>Kode Organisasi</label>
                        <select class="full-width" name="kodeorg" onchange="" title="Kode Organisasi" search="true">
                            <?= $optionsUnit ?>
                        </select>
                    </div>
                    <!-- <div class="col-xl-4 col-md-4 col-sm-6 col-xs-12 u-margin-b-10" style="display:none;">
                        <label>Divisi</label>
                        <select class="full-width" name="divisi" onchange="" title="Divisi" search="false">
                        </select>
                    </div>
                    <div class="col-xl-6 col-md-6 col-sm-6 col-xs-12 u-margin-b-10" style="display:;">
                        <label>Tanggal Selesai</label>
                        <input class="full-width" type="text" name="tanggal2" value="" readonly="readonly" onmousemove="setCalendar(this,'%d-%m-%Y')" autocomplete="off" title="Tanggal Akhir">
                    </div> -->

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
}
?>