<?
defined('BASEPATH') or exit('No direct script access allowed');
class Setupmandor extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Setup_mandor');
        $this->load->model('Setup_datakaryawan');
        $this->load->model('Setup_jabatan');
    }

    function slave()
    {
        $case = $this->get('switcher');
        switch ($case) {
            case 'pdf':
            case 'excel':
            case 'csv':
            case 'filter':
                // echo $this->Filter();
                break;
            default:
                //get request halaman
                $page = ((int)$this->get('page') == 0) ? 1 : (int)$this->get('page');
                ////////////////////////////
                //Pengambilan Data Total Row

                $getData = $this->Setup_mandor->selectQuery();
                //Pagination setup and load
                $this->load->lib("Pagination", "paging");
                $tab = $this->lib->paging;
                // $tab->id = "vhc";
                $tab->total_rows = $getData->rowCount();
                //row has definition
                $tab->per_page = 30; //LIMIT : default 20
                $tab->cur_page = $page;
                $starting_limit = ($page - 1) * $tab->per_page;

                // echo $starting_limit;
                //get Data per page == 
                // $tab->type_load = 'AUTO';
                $dataTable =  $this->Datalist([$starting_limit,  $tab->per_page]);
                //create HTML to json
                $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                //Build HTML
                $tab->build();
                //Load HTML  
                $tab->loadHTML();
                //Process End
                break;
            case 'view':
                $mandorid = $this->get('mandorid');
                $this->getView($mandorid);
                break;
        }
    }

    function getView($mandorid)
    {
        $dataKemandoran = $this->Setup_mandor->getKemandoran("where mandorid = '{$mandorid}' and statusaktif = '1'");
        $karyawanIds = array_column($dataKemandoran, 'karyawanid');
        $setupKaryawan = $this->Setup_datakaryawan->selectOptDetail("WHERE karyawanid IN ('".implode("','",$karyawanIds) ."')");
        $jabatan = $this->Setup_jabatan->selectataAktif();
        $jabatanKey = array_column($jabatan, 'namajabatan','kodejabatan');
        $mandor = $this->Setup_datakaryawan->selectOpt("WHERE karyawanid IN ('".$mandorid."')");
   
        ?>
        <fieldset style='min-height:100%;padding:30px;margin:0'>
            <p style="margin: 3px 0 6px;text-align: left;text-decoration: underline; font-weight:600">Kemandoran <?=$mandor[$mandorid]?></p>
            <table cellpadding=3 cellspacing=1 class=sortable width=100%>
                <thead>
                    <tr class=rowheader>
                        <td align=center>No</td>
                        <td align=center>NIK</td>
                        <td align=center>Nama</td>
                        <td align=center>Jabatan</td>
                        <td align=center>Divisi</td>
                    </tr>
                </thead>
                <tbody>
                    <? $nomor = 0;
                    if (count($setupKaryawan) > 0) {
                        foreach ($setupKaryawan as $key => $value) {
                            $nomor++;
                            // pre_arr($value)
                    ?>
                            <tr class=rowcontent style=vertical-align:top;>
                                <td align=center><? echo $nomor ?></td>
                                <td align=center><? echo $value['nik'] ?></td>
                                <td align=center><? echo $value['namakaryawan'] ?></td>
                                <td align=center><? echo $jabatanKey[$value['kodejabatan']] ?></td>
                                <td align=center><? echo $value['subbagian'] ?></td>
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
    // function Filter()
    // {
    //     $tab = $this->get('switcher');
    //     $filter = "<input type='hidden' id='proses' name='proses' value='insert' />
    //     <div id='entryForm'>
    //         <fieldset style='float:left'>
    //             <legend>Cari Data</legend>
    //             <table cellspacing='1' border='0'>
    //                 <tr>
    //                     <td>Kebun</td>
    //                     <td>:</td>
    //                     <td><select id='idKbn' name='idKbn' style='width:170px;'></select></td>

    //                     <td>Divisi</td>
    //                     <td>:</td>
    //                     <td><select id='idDivisi' name='idDivisi' style='width:170px;'></select></td>
    //                     <td rowspan='2'>
    //                     <td rowspan='3' id='tmblHeader'>
    //                         <button class=mybutton id='dtl_pem' onclick='previewData();'>Cari</button>
    //                     </td>
    //                 </tr>
    //             </table>
    //         </fieldset>
    //     </div>";
    //     return (object)$filter;
    // }

    function Datalist($pageLimit)
    {

        $thead =  "<thead>
        <tr class=\"rowheader\">
        <th align=center>No</th>		
        <th align=center>Kode</th>		
        <th align=center>Nama</th>		
        <th align=center>Divisi</th>					
        </tr>
        </thead>";
        //Pengambilan data
        $uriUpdate = "?switcher=form&for=update";
        $uriView = "?switcher=view";
        $uriDelete = "?switcher=delete";
        $uriCancel = "?switcher=cancel";
        $uriPosting = "?switcher=posting";
        $uriunPosting = "?switcher=unposting";
        $r = $this->Setup_mandor->selectdata($pageLimit);
        $table = "";
        if (count($r) > 0) {
            $no = 0;
            foreach ($r as $k => $v) {
                $no++;
                $action = array();
                $action['view'] = "listAction('$uriView&mandorid={$v['mandorid']}');";
                $action = $this->toAtrr($action);
                $table .= "<tr class=\"rowcontent\" list-action " . $action . ">";
                $table .= "<td align=\"center\">{$no}</td>";
                $table .= "<td align=\"left\">" . (int)$v['mandorid'] . "</td>";
                $table .= "<td align=\"left\">{$v['namakaryawan']}</td>";
                $table .= "<td align=\"left\">{$v['subbagian']}</td>";
                $table .= "</tr>";
            }
        } else {
            $table .= "<tr>";
            $table .= "<td align=\"center\" colspan=\"9\">No data</td>";
            $table .= "</tr>";
        }

        $result['head'] = $thead;
        $result['body'] = $table;
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
