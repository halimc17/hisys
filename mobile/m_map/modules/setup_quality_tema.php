<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Setup_quality_tema extends OWL_Controller{
    public function __construct(){
		parent::__construct();
		$this->load->model('Setup_poligon');
    }
    function slave(){
        $case   = $this->get('switcher');
        $method = $this->get('method');
        
        
        switch($method){
            case 'save':
                $title=$this->get('title');
                $color=$this->get('color');
                $keterangan=$this->get('keterangan');
                $this->Setup_poligon->saveDataQualityTema([$title,urldecode($color),$keterangan]);
            break;
        }
        switch($case){
            case'new':?>
                <form method=get>
                    <div class="body-frame u-margin-10">
                        <div class="row">
                            <div class="col-12 u-margin-b-10">
                                <label>Title:</label>
                                <input class="full-width" type="text" name="title" required>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-4 u-margin-b-10">
                                <label>Color:</label>
                                <input type="color" class="colorpicker" id="color" name="color" value="#ff0000">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-12 u-margin-b-10">
                                <label>Keterangan:</label>
                                <input type="text" class="full-width" id="keterangan" name="keterangan" value="">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-12 u-margin-b-10">
                                <input class="mybutton col-3 col-md-3 col-sm-3 col-xs-3 u-margin-r-10" type="submit" name="method" value="Save">
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </form>
                <?
            break;
            case'filter':
            break;
            case'form':
            break;
            case'view': 
                $this->LoadView('1');
            break;
            default:
                $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
                ////////////////////////////
                //Pengambilan Data Total Row
                $getData = $this->Setup_poligon->selectQueryQuality();
                
                //Pagination setup and load
                $this->load->lib("Pagination","paging");
                $tab = $this->lib->paging;
                $tab->id = "Quality Tema";
                $tab->total_rows = ($getData)?$getData->rowCount():0;
                
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
            
        }
    }
	function Datalist($pageLimit){
        $thead =  "<thead>";
            $thead .= "<tr class='rowheader'>";
            $thead .=    "<th align='center'>NO</th>";	
            $thead .=    "<th align='center'>TITLE</th>";	
            $thead .=    "<th align='center'>COLOR</th>";
            $thead .=    "<th align='center'>KETERANGAN</th>";
            $thead .=  "</tr>";
        $thead .=  "</thead>"; 
        //Pengambilan data
        $uriUpdate = "?switcher=form&for=upload";
        $uriView = "?switcher=view";
        $uriDelete = "?switcher=delete";
        $uriPosting = "?switcher=posting";
        $r = $this->Setup_poligon->selectDataQuality($pageLimit);
        $n = 1;
        $table = "";
        if(count($r) > 0){
            $no = 0 + $pageLimit[0];
            foreach($r as $k=>$v){
                $no++;
                $action =array();
                // $action['delete'] = "deleteAction('{$uriDelete}&id={$v['id']}');";
                $action = $this->toAtrr($action);
                $table .= "<tr class=\"rowcontent\" list-action ".$action.">";
                $table .= "<td align=\"center\" width=\"1\">{$no}</td>";
                $table .= "<td align=\"center\"><strong>{$v['TITLE']}</strong></td>";
                $table .= "<td align=\"center\"><div style=\"background:{$v['COLOR']}; height:15px;width:50px;padding:5px 10px;\">{$v['COLOR']}</div></td>";
                $table .= "<td align=\"left\">{$v['KET']}</td>";
               
                $table .= "</tr>";
			}
        }else{
            $table .= "<tr>";
            $table .= "<td align=\"center\" colspan=\"8\">No data</td>";
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
        $d['slave'] = "new";
        $d['text'] = "new";
        $d['width'] = "300px";
        $d['window'] = "right";
        $d['show'] = TRUE;
        $d['isEnable'] = TRUE;
        $option['buatbaru'] = $d;

        $d=array();
        $d['title'] = "List Data";
        $d['text'] = "List Data";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['listdata'] = $d;

        $d=array();
        $d['title'] = "filter";
        $d['slave'] = "filter";
        $d['text'] = "filter";
        $d['width'] = "300px";
        $d['show'] = true;
        $d['isEnable'] = true;
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