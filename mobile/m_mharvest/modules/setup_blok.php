<?
defined('BASEPATH') or exit('No direct script access allowed');
class Setup_blok extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Blok');
        $this->load->model('Blok');
    }

    function slave()
    {
        $case = $this->get('switcher');

        switch ($case) {
            case 'pdf':
            case 'excel':
            case 'csv':
            default:
                //get request halaman
                $page = ((int)$this->get('page') == 0) ? 1 : (int)$this->get('page');

                ////////////////////////////
                //Pengambilan Data Total Row
                // $getData = $this->Blok->selectQuery();
                $whr = "WHERE (kodeorg like '" .  $_SESSION['empl']['lokasitugas'] . "%' and statusblok <> 'TB') or (statusblok = 'TB')";
                $getData = $this->Blok->getDataBlokMobile($whr);      
                
                
                //Pagination setup and load
                $this->load->lib("Pagination", "paging");
                $tab = $this->lib->paging;
                $tab->id = "setup_blok";
                // $tab->total_rows = $getData->rowCount();
                $tab->total_rows =count($getData);
                //row has definition
                $tab->per_page = 30; //LIMIT : default 20
                $tab->cur_page = $page;
                $starting_limit = ($page - 1) * $tab->per_page;

                //get Data per page == 
                // $tab->type_load = 'AUTO';
                $dataTable =  $this->Datalist([$starting_limit, $tab->per_page],$whr);
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
                break;
            case 'filter':
                // echo $this->Filter();
                break;
        }
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

    function Datalist($pageLimit,$whr)
    {

        $thead =  "<thead>
        <tr class=\"rowheader\">
        <th align=center>Blok</th>		
        <th align=center>Tahun Tanam</th>		
        <th align=center>Luas Planted</th>		
        <th align=center>Luas Unplanted</th>		
        <th align=center>Jumlah Pokok</th>		
        <th align=center>Status Blok</th>		
        <th colspan='2';align=center>Mulai Panen</th>		
        <th align=center>Kode Tanah</th>		
        <th align=center>Klasifikasi Tanah</th>		
        <th align=center>Topografi</th>		
        <th align=center>Inti/Plasma</th>		
        <th align=center>Jenis Bibit</th>		
        <th align=center>Cadangan</th>		
        <th align=center>Okupasi</th>		
        <th align=center>Rendahan</th>		
        <th align=center>Sungai</th>		
        <th align=center>Rumah</th>		
        <th align=center>Kantor</th>		
        <th align=center>Pabrik</th>		
        <th align=center>Jalan</th>		
        <th align=center>Kolam</th>		
        <th align=center>Umum</th>		
        <th align=center>Areal Berbatu</th>		
        <th align=center>Konserfasi</th>		
        <th align=center>Setup Blok</th>		
        </tr>
        </thead>";
        //Pengambilan data
        $uriUpdate = "?switcher=form&for=update";
        $uriView = "?switcher=view";
        $uriDelete = "?switcher=delete";
        $uriCancel = "?switcher=cancel";
        $uriPosting = "?switcher=posting";
        $uriunPosting = "?switcher=unposting";
        $r = $this->Blok->selectdata($pageLimit,$whr);
        $table = "";
        if (count($r) > 0) {
            // $no = 0;
            foreach ($r as $k => $v) {
                // $no++;
                $table .= "<tr class=\"rowcontent\">";
                // $table .= "<td align=\"center\">{$no}</td>";
                $table .= "<td align=\"left\">{$v['kodeorg']}</td>";
                $table .= "<td align=\"left\">{$v['tahuntanam']}</td>";
                $table .= "<td align=\"left\">{$v['luasareaproduktif']}</td>";
                $table .= "<td align=\"left\">{$v['luasareanonproduktif']}</td>";
                $table .= "<td align=\"left\">{$v['jumlahpokok']}</td>";
                $table .= "<td align=\"left\">{$v['statusblok']}</td>";
                $table .= "<td align=\"left\">{$v['bulanmulaipanen']}</td>";
                $table .= "<td align=\"left\">{$v['tahunmulaipanen']}</td>";
                $table .= "<td align=\"left\">{$v['kodetanah']}</td>";
                $table .= "<td align=\"left\">{$v['klasifikasitanah']}</td>";
                $table .= "<td align=\"left\">{$v['topografi']}</td>";
                $table .= "<td align=\"left\">{$v['intiplasma']}</td>";
                $table .= "<td align=\"left\">{$v['jenisbibit']}</td>";
                $table .= "<td align=\"left\">{$v['cadangan']}</td>";
                $table .= "<td align=\"left\">{$v['okupasi']}</td>";
                $table .= "<td align=\"left\">{$v['rendahan']}</td>";
                $table .= "<td align=\"left\">{$v['sungai']}</td>";
                $table .= "<td align=\"left\">{$v['rumah']}</td>";
                $table .= "<td align=\"left\">{$v['kantor']}</td>";
                $table .= "<td align=\"left\">{$v['pabrik']}</td>";
                $table .= "<td align=\"left\">{$v['jalan']}</td>";
                $table .= "<td align=\"left\">{$v['kolam']}</td>";
                $table .= "<td align=\"left\">{$v['umum']}</td>";
                $table .= "<td align=\"left\">{$v['arealberbatu']}</td>";
                $table .= "<td align=\"left\">{$v['konservasi']}</td>";
                $table .= "<td align=\"left\">-</td>";
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
