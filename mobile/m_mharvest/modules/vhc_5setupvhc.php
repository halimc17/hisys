<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Vhc_5setupvhc extends OWL_Controller{
    public function __construct(){
		parent::__construct();
		$this->load->model('Setup_vhc');
        $this->load->lib("Qrlib");
    }
    function slave(){
      $case = $this->get('switcher');
        switch($case){
			case'pdf':
                //header
            case'excel':
                //header
            case'csv':
                //header
            default:
                //get request halaman
                $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
                ////////////////////////////
                //Pengambilan Data Total Row
                $getData = $this->Setup_vhc->selectQuery();
                
                //Pagination setup and load
                $this->load->lib("Pagination","paging");
                $tab = $this->lib->paging;
                $tab->id = "vhc";
                $tab->total_rows = $getData->rowCount();
                //row has definition
                $tab->per_page = 30;//LIMIT : default 20
                $tab->cur_page = $page;
                $starting_limit = ($page-1)*$tab->per_page;
                //get Data per page == 
				// $tab->type_load = 'AUTO';
                $dataTable =  $this->Datalist([$starting_limit,$tab->per_page]);
                //create HTML to json
                $tab->THEAD = $tab->convHtmlToArray($dataTable->head);
                $tab->TBODY = $tab->convHtmlToArray($dataTable->body);
                //Build HTML
                $tab->build();        
                //Load HTML  
                $tab->loadHTML();
                //Process End
            break;
            case'view':
            break;
        }
    }
	function Datalist($pageLimit){
        
        $thead =  "<thead>
                        <tr class=\"rowheader\">
                        <th align=center>No</th>	
                        <th align=center>CODE</th>	
                        <th align=center>NUMBER</th>
                        <th align=center>QR</th>
                        <th align=center>UNIT</th>
                        <th align=\"center\">TYPE</th>
                        <th align=center>STATUS</th>
                    </tr>
                </thead>"; 
        //Pengambilan data
        $uriUpdate = "?switcher=form&for=update";
        $uriView= "?switcher=view";
        $uriDelete = "?switcher=delete";
        $uriCancel = "?switcher=cancel";
        $uriPosting = "?switcher=posting";
        $uriunPosting = "?switcher=unposting";
        $r = $this->Setup_vhc->selectdata($pageLimit);
        // print_r($r);
        $n = 1;
        $table = "";
        if(count($r) > 0){
            // $no = 0 ;
            $no = 0 + $pageLimit[0];
            foreach($r as $k=>$v){
                $no++;
                $action =array();
                // $action['view'] = "viewAction('{$uriView}&id={$v['kodevhc']}');";
				// if($v['status'] == 0){
				// 	$action['edit'] = "editAction('{$uriUpdate}&id={$v['kodevhc']}');";
				// 	$action['delete'] = "deleteAction('{$uriDelete}&id={$v['kodevhc']}');";
				// }
                // $action = $this->toAtrr($action);
                if($v['status'] == 1){
                    $sttd = "ACTIVE";	
                }else{
                    $sttd = "INACTIVE";
                }
                // if($v['kepemilikan'] == 1){
                //     $dptk = "OWN";	
                // }else{
                //     $dptk = "RENT";
                // }
                $sourceQr = "-";
                if($v['nopol'] != ""){
                    ob_start();
                    QRcode::png($v['nopol']);
                    $imgData=ob_get_clean();
                    $sourceQr = '<img id="qr_'.$no.'" name="'.$v['nopol'].'" src="data:image/png;base64,'.base64_encode($imgData).'" />';
                }
                $table .= "<tr class=\"rowcontent\" list-action ".$action.">";
                $table .= "<td align=\"center\">{$no}</td>";
                $table .= "<td align=\"left\">{$v['kodevhc']}</td>";
                $table .= "<td align=\"center\">".($v['nopol'] != "" ? $v['nopol']:"-")."</td>";
                $table .= "<td align=\"center\">{$sourceQr}</td>";
                $table .= "<td align=\"left\">{$v['kodeorg']}</td>";
                $table .= "<td align=\"left\">{$v['namajenisvhc']}</td>";;
                $table .= "<td align=\"left\">{$sttd}</td>";
                $table .= "</tr>";
			}
        }else{
            $table .= "<tr>";
            $table .= "<td align=\"center\" colspan=\"9\">No data</td>";
            $table .= "</tr>";
        }
        $result['head'] = $thead;
        $result['body'] = $table;
        return (object)$result;
    }

	function options($SELF,$breadcrumb){
        $option = array();
        $option['master']       = '#bodymaster';
        $option['slave']        = $this->site_url().$this->uri->uri_string."_slave";
        $option['getpage']      = 'switcher';
        $option['type']         = '';
        $option['javascript']['src'] = array($this->base_url().'js/'.$SELF.'.js?version='.time().'');

        $d=array();
        $d['title'] = "Form Entry Data";
        $d['slave'] = "form";
        $d['text'] = "new";
        $d['show'] = false;
        $d['isEnable'] = true;
        $option['buatbaru'] = $d;

        $d=array();
        $d['title'] = "List Data";
        $d['text'] = "List Data";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['listdata'] = $d;

        $d=array();
        $d['title'] = "Filter";
        $d['slave'] = "Filter";
        $d['text'] = "Filter";
        $d['width'] = "300px";
        $d['show'] = true;
        $d['isEnable'] = false;
        $option['filter'] = $d;

        $option['breadcrumb']['title'] = $breadcrumb;

        $option['excel']['show']= false;
        $option['pdf']['show']= false;
        $option['csv']['show']= false;
        $option['fixHeader']['show']= false;
        $option['actions'] = array();
        $option['pathinfo']['site_url'] = $this->site_url();
        $option['pathinfo']['base_url'] = $this->base_url();
        $OPT =  json_encode($option);
        return $OPT;
    }
}