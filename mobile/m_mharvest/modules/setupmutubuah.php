<?
defined('BASEPATH') or exit('No direct script access allowed');
class Setupmutubuah extends OWL_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mmutu');
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
                $where = "WHERE jenis = 'Mutu Buah' and aktif ='1'";
                $getData = $this->Mmutu->getMutu($where);
                
                //Pagination setup and load
                $this->load->lib("Pagination", "paging");
                $tab = $this->lib->paging;
                // $tab->id = "vhc";
                $tab->total_rows =count($getData);
                //row has definition
                $tab->per_page = 30; //LIMIT : default 20
                $tab->cur_page = $page;
                $starting_limit = ($page - 1) * $tab->per_page;

                // echo $starting_limit;
                //get Data per page == 
                // $tab->type_load = 'AUTO';
                $dataTable =  $this->Datalist([$starting_limit, $tab->per_page],$where);

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

    function Datalist($pageLimit,$where)
    {

        $thead =  "<thead>
        <tr class=\"rowheader\">
        <th align=center>Kode</th>	
        <th align=center>Keterangan</th>					
        <th align=center>Satuan</th>	
        </tr>
        </thead>";
        //Pengambilan data
        $uriUpdate = "?switcher=form&for=update";
        $uriView = "?switcher=view";
        $uriDelete = "?switcher=delete";
        $uriCancel = "?switcher=cancel";
        $uriPosting = "?switcher=posting";
        $uriunPosting = "?switcher=unposting";
        $r = $this->Mmutu->selectdata($pageLimit,$where);
        $table = "";
        if (count($r) > 0) {
            // $no = 0;
            foreach ($r as $k => $v) {
                // $no++;
                $table .= "<tr class=\"rowcontent\">";
                // $table .= "<td align=\"center\">{$no}</td>";
                $table .= "<td align=\"left\">".($v['kode'] == ''?$v['idjenis']:$v['kode'])."</td>";
                $table .= "<td align=\"left\">{$v['kriteria']}</td>";
                $table .= "<td align=\"left\">{$v['satuan']}</td>";
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
