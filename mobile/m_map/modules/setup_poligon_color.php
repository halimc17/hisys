<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Setup_poligon_color extends OWL_Controller{
    public function __construct(){
		parent::__construct();
		$this->load->model('Setup_poligon');
    }
    function slave(){
        $case   = $this->get('switcher');
        switch($case){
            case 'save':
                $title=$this->get('title');
                $fillcolor=$this->get('fillcolor');
                $strokecolor=$this->get('strokecolor');
                $result=$this->Setup_poligon->saveData([$title,urldecode($fillcolor),urldecode($strokecolor)]);

                echo json_encode($result);
            break;
            case'new': ?>
                 <form id="submitColor" method=get onkeydown="return event.key != 'Enter';" callback="hasil">
                    <div class="body-frame u-margin-10">
                        <div class="row">
                            <div class="col-12">
                                <div class="f-grid f-load ">
                                        <div class="custom-review f-grid-item f-block-width es-padding">
                                            <div class="es-wrapper es-box es-online" tabindex="0">
                                                <div class="es-background"></div>  
                                                <div class="es-user">
                                                    <div id="previewColor" class="es-avatar img-75" style="background-color:#ff0000;border: thick solid #000000"></div>
                                                </div>
                                                <div class="es-meta">
                                                    <div id="" class="es-date"> Preview </div>
                                                </div>
                                        </div>
                                        <div class="clearfix"></div>

                                        <div class="row u-margin-b-10 u-margin-t-10">
                                            <div class="list-group-item list-group-item-action py-3 lh-tight">
                                                <div class="col-6 mb-1 u-bottom-10 small">Title:</div>
                                            </div>
                                            <div class="col nav-link active py-3 border-bottom "><input id="titlecolor" oninput="changeColor(this.value);" class="full-width u-margin-t-5" type="text" name="title" required></div>
                                        </div>
                                        <div class="row">
                                            <div class="list-group-item list-group-item-action py-3 lh-tight">
                                                <div class="col-6 mb-1 u-bottom-10 small">Stroke color:</div>
                                            </div>
                                            <div class="col"><input type="color" id="strokecolor" oninput="changeColor(this.value);" name="strokecolor" value="#000000" /></div>
                                        </div>
                                        <div class="row u-margin-t-10 u-margin-b-20">
                                            <div class="list-group-item list-group-item-action py-3 lh-tight">
                                                <div class="col-6 mb-1 u-bottom-10 small">Fill color:</div>
                                            </div>
                                            <div class="col nav-link active py-3 border-bottom"><input type="color" id="fillcolor" oninput="changeColor(this.value);" name="fillcolor" value="#ff0000" /></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-12 col-md-12 col-sm-12 col-xs-12 u-margin-b-10">
                                                <input class="mybutton col-12 u-margin-b-10" type="submit" placeholder="" value=save name=switcher>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                $getData = $this->Setup_poligon->selectQuery();
                //Pagination setup and load
                $this->load->lib("Pagination","paging");
                $tab = $this->lib->paging;
                $tab->id = "Poligon Color";
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
            $thead .=    "<th align='left'>TITLE</th>";	
            $thead .=    "<th align='center'>COLOR</th>";
            $thead .=  "</tr>";
        $thead .=  "</thead>"; 
        //Pengambilan data
        $uriUpdate = "?switcher=form&for=upload";
        $uriView = "?switcher=view";
        $uriDelete = "?switcher=delete";
        $uriPosting = "?switcher=posting";
        $r = $this->Setup_poligon->selectDataPoligon($pageLimit);
        $n = 1;
        $table = "";
        if(count($r) > 0){
            $no = 0 + $pageLimit[0];
            foreach($r as $k=>$v){
                $no++;
                $action =array();
                $action = $this->toAtrr($action);
                $table .= "<tr class=\"rowcontent\" list-action ".$action.">";
                $table .= "<td align=\"center\" width='1'>{$no}</td>";
                $table .= "<td align=\"left\"><strong>{$v['TITLE']}</strong></td>";
                $table .= "<td align=\"center\" width='20%'><div style=\"background:{$v['FILLCOLOR']}; height:15px;width:15px; border: thick solid {$v['STROKECOLOR']};border-radius:15px;\"></div></td>";
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