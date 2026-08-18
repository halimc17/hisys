<?
defined('BASEPATH') or exit('No direct script access allowed');
class Kebun_kehadiranumum extends OWL_Controller
{
    protected $pathkebun = "kebun/";
    protected $mharvest = "mharvest/";
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_datakaryawan');
        $this->load->model('K_kehadiranUmum');
        // $this->load->model('Privilege', 'priv');
        // $this->SetupMutu = load_class('Mmutu', $this->mharvest);
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
                $detailKehadiran   = $this->K_kehadiranUmum->selectDetail('where notransaksi="'.$notransaksi.'"');
                if ($detailKehadiran and count($detailKehadiran) > 0) {
                    echo "<table cellpadding=3 cellspacing=1 class=sortable width=100%>";
                    echo "<thead>
                        <tr class=rowheader>
                            <td align=center>No</td>
                            <td align=center>Nama Karyawan</td>
                            <td align=center>Absensi</td>
                            <td align=center>Jumlah HK</td>
                            <td align=center>Insentif</td>
                            <td align=center>Keterangan</td>
                            </tr>";
                            
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    // $grandtotal=[];
                    if ($detailKehadiran and count($detailKehadiran) > 0) {
                        $num=1;
                        $jmlHk=0;
                        $jmlInsentif=0;
                        foreach ($detailKehadiran as $key => $dt_value) {
                            $jmlHk+=$dt_value['jhk'];
                            $jmlInsentif+=$dt_value['insentif'];
                            $namakaryawan=$this->Setup_datakaryawan->selectOpt("where karyawanid='".$dt_value['nik']."'");
                            // echo "<pre>";
                            // echo print_r($dt_value);
                            // echo "</pre>";
                            echo "<tr class=rowcontent style=vertical-align:top;>";
                            echo "<td align=center>".$num."</td>";
                            echo "<td align=left><strong>".$namakaryawan[$dt_value['nik']]." </strong></td>";
                            echo "<td align=center><strong>".$dt_value['absensi']." </strong></td>";
                            echo "<td align=right>".$dt_value['jhk']." </td>";
                            echo "<td align=right>".$dt_value['insentif']." </td>";
                            echo "<td align=left>".$dt_value['keterangan']." </td>";
                            echo "</tr">
                            $num++;
                        }
                    }
                    echo "</tbody>";
                    echo "<tfoot>";
                        echo "<tr style=vertical-align:top;>";
                        echo "<td colspan=3 align=right><strong>TOTAL</strong></td>";
                        echo "<td  align=right>".$jmlHk."</td>";
                        echo "<td  align=right>".$jmlInsentif."</td>";
                        echo "<td  align=right></td>";
                        echo "</tr>";
                    echo "</tfoot>";
                    echo "<tbody>";
                    echo "</table>";
                }
                
                break;
            default:
            $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
            $page = (int)$this->get('page');
            ////////////////////////////
            //Pengambilan Data Total Row
            $getData = $this->K_kehadiranUmum->selectHeader('where tipetransaksi="ABS"');
            // print_r($getData);
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
                $dataTable = $this->getTable($this->K_kehadiranUmum->selectHeader('where tipetransaksi="ABS"',[$tab->cur_page, $tab->per_page]));
                //create HTML to json
                $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                //Build HTML
                $tab->build();
                //Load HTML  
                $tab->loadHTML();
                // Process End
            }
            break;
            case 'form':
                $this->formFormat();
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

    function getTable($data){
        // echo $data;
        // print_r($data);
        // No.	No Transaksi	No. Referensi	Unit	Kemandoran / Vendor	Tanggal	Jjg	Mandor	Mandor 1	Asisten	Approval	Kerani Panen	Last Update	Tanggal Input	Tanggal Stages	Status Stages	Posting By	Tanggal Posting	Aksi
        // echo($this->priv->imAdmin());
        $thead =  "<thead>
                    <tr class=\"rowheader\">
                        <th align=\"center\">No</th>
                        <th align=\"center\">Transaksi</th>
                        <th align=\"center\">Tanggal</th>
                        <th align=\"center\">Mandor</th>
                    </tr>
                </thead>";
        // $datakar = $this->Setup_datakaryawan->selectOpt();
        $n = 1;
        $dataTable = "";
        if ($data->rowCount() > 0) {
            // echo "sini";
            while ($v = $data->fetch()) {
                $act = array();
                $act['view'] = array();
                $namamandor=$this->Setup_datakaryawan->selectOpt("where karyawanid='".$v->nikmandor."'");
                // print_r($namamandor);
                // print_r($v);
                $act['view'] = "listAction('?switcher=view&id={$v->notransaksi}');";
                $action = $this->toAtrr($act);
                $dataTable .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $dataTable .= "<td align=\"center\">{$n}</td>";;
                $dataTable .= "<td align=\"left\">{$v->notransaksi}</td>";
                $dataTable .= "<td align=\"center\">{$v->tanggal}</td>";
                $dataTable .= "<td align=\"center\">{$namamandor[$v->nikmandor]}</td>";
                $dataTable .= "</tr>";
                $n++;
            }
        }else{
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