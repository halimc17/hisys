<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_mutuancak extends OWL_Controller
{
    protected $pathkebun = "kebun/";
    protected $mharvest = "mharvest/";
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_datakaryawan');
        $this->load->model('Mhancak');
        // $this->load->model('SetupMutu');
        $this->load->model('Privilege', 'priv');
        $this->SetupMutu = load_class('Mmutu', $this->mharvest);
        // $this->setup_kebun = load_class('Setup_kebun', $this->pathkebun);
    }

    function deleteTxn($notransaksi)
    {
        $whr = "notransaksi='" . $notransaksi . "'";
        echo $whr . ' ' . $notransaksi;

        $this->Mhancak->deleteMutuhancak($whr);
    }

    function postingTransaksi($notransaksi)
    {
        $whr = "notransaksi='" . $notransaksi . "'";
        $this->Mhancak->postingMutuhancak($whr);
    }

    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'pdf':
            case 'excel':
            case 'csv':
            case 'view':
                $data=[];
                $kodemutu=[];
                $notransaksi = $this->get('id');
                $jenismutu   = $this->SetupMutu->getMutu('where jenis like "%Mutu Hancak%"');
                $trxDataMutu = $this->Mhancak->selectQueryDetail('where notransaksi="'.$notransaksi.'"');
                if ($jenismutu and count($jenismutu) > 0) {
                    echo "<table cellpadding=3 cellspacing=1 class=sortable width=100%>";
                    echo "<thead>
                        <tr class=rowheader>
                            <td align=center rowspan=2>No</td>
                            <td align=center rowspan=2>Nama Karyawan</td>
                            <td align=center rowspan=2>Blok</td>
                            <td align=center rowspan=2>Tanggal Panen</td>
                            <td align=center colspan=".(count($jenismutu)+1).">Mutu Hancak</td>
                            </tr>";
                            foreach ($jenismutu as $key => $value) {
                                $kodemutu[$value['kode']]=$value['kode'];
                                echo "<td align=center >".$value['kriteria']."</td>";
                            }
                            
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    $grandtotal=[];
                    if ($trxDataMutu and count($trxDataMutu) > 0) {
                        foreach ($trxDataMutu as $key => $dt_value) {
                            $data[$dt_value['nik']]                 =[];
                            $data[$dt_value['nik']]['nik']          =[];
                            $data[$dt_value['nik']]['namakaryawan'] =[];
                            $data[$dt_value['nik']]['kodeorg']      =[];
                            $data[$dt_value['nik']]['tglpanen']     =[];
                            $data[$dt_value['nik']]['kodedenda']    =[];
                            $grandtotal[$kodemutu[$value]]         =0;
                            $data[$dt_value['nik']][$dt_value['kodedenda']]=0;
                            $karyawanNM = $this->Setup_datakaryawan->selectdatakaryawan([],"where karyawanid='{$dt_value['nik']}'");
                            foreach ($karyawanNM as $key => $value) {
                                $data[$dt_value['nik']]['namakaryawan']=$value['namakaryawan'];
                                
                            }
                            $data[$dt_value['nik']]['kodeorg']=$dt_value['kodeorg'];
                            $data[$dt_value['nik']]['tglpanen']=$dt_value['tglpanen'];
                            $data[$dt_value['nik']]['nik']=$dt_value['nik'];
                        }
                        
                        foreach ($trxDataMutu as $key => $dt_value) {
                            $data[$dt_value['nik']][$dt_value['kodedenda']]+=$dt_value['nilai'];
                            $grandtotal[$dt_value['kodedenda']]+=$dt_value['nilai'];
                            $data[$dt_value['nik']]['kodedenda'][]=$dt_value['kodedenda'];
                        }
                        
                        $num=0;
                        foreach ($data as $key => $value) {
                            $num++;
                            echo "<tr class=rowcontent style=vertical-align:top;>";
                            echo "<td align=center><strong>".$num." </strong></td>";
                            echo "<td align=left ><strong>".$value['namakaryawan']."</strong></td>";
                            echo "<td align=center >".$value['kodeorg']."</td>";
                            echo "<td align=center >".$value['tglpanen']."</td>";
                            foreach ($kodemutu as $keym => $valuem) {
                                if(isset($kodemutu[$valuem])and count($value[$kodemutu[$valuem]])>0){
                                    echo "<td align=center >".$value[$kodemutu[$valuem]]."</td>";
                                }else{
                                    echo "<td align=center >-</td>";
                                }
                            }
                            echo "</tr>";
                        }
                    }else {
                        echo "<tr class=rowcontent style=vertical-align:top;>";
                        echo "<td align=center colspan=".(count($jenismutu)+4)." >Tidak ada data</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "<tfoot>";
                    if ($trxDataMutu and count($trxDataMutu) > 0) {
                        echo "<tr style=vertical-align:top;>";
                        echo "<td colspan=4 align=right><strong>TOTAL</strong></td>";
                        foreach ($kodemutu as $keym => $valuem) {
                            if(isset($kodemutu[$valuem]) and count($grandtotal[$kodemutu[$valuem]])>0){
                                echo "<td align=center >".$grandtotal[$kodemutu[$valuem]]."</td>";
                            }else{
                                echo "<td align=center >-</td>";
                            }
                        }
                        echo "</tr>";
                    }
                    echo "</tfoot>";
                    echo "<tbody>";
                    echo "</table>";
                }
                
                break;
            default:
            // $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
            $page = (int)$this->get('page');
            ////////////////////////////
            //Pengambilan Data Total Row
            $getData = $this->Mhancak->selectQuery();
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
                $dataTable = $this->getTable($this->Mhancak->selectQuery([$tab->cur_page, $tab->per_page]));
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
                $this->getView();
                break;
            case 'Filter':
                break;
            case 'insert':
                break;
            case 'delete':
                $notransaksi = $this->get('notransaksi');
                $this->deleteTxn($notransaksi);
                break;
            case 'posting':
                $notransaksi = $this->get('notransaksi');
                $this->postingTransaksi($notransaksi);
                break;
        }
    }

    function getView()
    {
        function pre_arr($arr)
        {
            echo "<pre>";
            echo print_r($arr);
            echo "</pre>";
        }
        $namakegiatan = $this->Bkm->bkmSetupKegiatan();
?>
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
        // No.	No Transaksi	No. Referensi	Unit	Kemandoran / Vendor	Tanggal	Jjg	Mandor	Mandor 1	Asisten	Approval	Kerani Panen	Last Update	Tanggal Input	Tanggal Stages	Status Stages	Posting By	Tanggal Posting	Aksi
        // echo($this->priv->imAdmin());
        $thead =  "<thead>
                    <tr class=\"rowheader\">
                        <th align=\"center\">No</th>
                        <th align=\"left\">Transaksi</th>
                        <th align=\"left\">Tgl Panen</th>
                        <th align=\"center\">Unit</th>
                        <th align=\"center\">Nik Mandor</th>	
                        <th align=\"center\">Processed</th>
                        <th align=\"center\">Flag</th>
                        <th align=\"center\">No Syncronized</th>
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
                $act['view'] = "listAction('?switcher=view&id={$v->notransaksi}');";
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
                $action = $this->toAtrr($act);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">{$n}</td>";;
                $dataTable .= "<td align=\"left\">{$v['notransaksi']}</td>";
                $dataTable .= "<td align=\"left\">{$v['tanggal']}</td>";
                $dataTable .= "<td align=\"left\">{$v['divisi']}</td>";
                $dataTable .= "<td align=\"left\">{$v['nikmandor']}</td>";
                $dataTable .= "<td align=\"left\">{$v['approved']}</td>";
                $dataTable .= "<td align=\"left\">{$v['flag']}</td>";
                $dataTable .= "<td align=\"left\">{$v['noreferensi']}</td>";
                $dataTable .= "<td align=\"left\">{$v['updatetime']}</td>";
                $dataTable .= "</tr>";
                $n++;
            }
            // foreach ($data as $k => $v) {
            //     $act = array();
            //     $act['view'] = "listAction('?switcher=view&id={$v['notransaksi']}');";
            //     if ($this->priv->imAdmin()) {
            //         if ($v['flag'] == 1) {
            //             $act['unposting'] = "listAction('?switcher=unposting&id={$v['notransaksi']}');"; //unposting
            //         } else {
            //             $act['delete'] = "listAction('?switcher=delete&id={$v['notransaksi']}');"; //delete
            //             $act['posting'] = "listAction('?switcher=posting&id={$v['notransaksi']}');"; //posting
            //             $act['update'] = "listAction('?switcher=update&id={$v['notransaksi']}');"; //posting
            //         }
            //     }
            //     $action = $this->toAtrr($act);
            //     $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
            //     $dataTable .= "<td align=\"center\">{$n}</td>";;
            //     $dataTable .= "<td align=\"left\">{$v['notransaksi']}</td>";
            //     $dataTable .= "<td align=\"left\">{$v['tanggal']}</td>";
            //     $dataTable .= "<td align=\"left\">{$v['divisi']}</td>";
            //     $dataTable .= "<td align=\"left\">{$v['nikmandor']}</td>";
            //     $dataTable .= "<td align=\"left\">{$v['flag']}</td>";
            //     $dataTable .= "<td align=\"left\">{$v['noreferensi']}</td>";
            //     $dataTable .= "<td align=\"left\">{$v['updatetime']}</td>";
            //     $dataTable .= "</tr>";
            //     $n++;
            // }
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