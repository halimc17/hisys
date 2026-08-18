<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Upload_filesvg extends OWL_Controller{
    public function __construct(){
		parent::__construct();
		$this->load->model('Uploadfile');
		$this->load->model('Setup_datakaryawan');
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
                $getData = $this->Uploadfile->selectQuery();
                
                //Pagination setup and load
                $this->load->lib("Pagination","paging");
                $tab = $this->lib->paging;
                $tab->id = "upload_filekml";
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
            
            case'form':
                $this->getFile();
            break;
            case'filter':
            break;
            case'view': 
                $this->LoadView($this->get('id'));
            break;
        }
    }
	function getFile(){ 
        $this->Uploadfile->upload();
    }
    function LoadView($id){ 
        $data = $this->Uploadfile->selectbyId($id);
        $file = Null;
        if(count($data)> 0){
            $data = array_shift($data);
            $file = $data['src'];
        ?>
        <div id="mapview" show="<?php echo $file;?>" style="width:60%;height:100%;position: absolute;left: 0px;">
         <?php include(VIEWPATH.'map_clean.php'); 
         ?>
        </div>
        <div style="width:40%;height:100%;position: absolute;right: 0px;">
            <table id="metadata" class="sortable data-table full-width">
                <thead>
                     <tr class="rowheader">
                        <th align="left">Komponen</th>	
                        <th align="left">Value</th>	
                    </tr>
                </thead>
                <tbody>
                    <tr><td align="left"><?php echo $data['namefile'].".".$data['mimes']; ?></td><td align="center"><?php echo $data['name']; ?></td></tr>
                    <tr><td align="left">Author</td><td align="center" id="author"></td></tr>
                    <tr><td align="left">Description</td><td align="center" id="description"></td></tr>
                    <tr><td align="left">Name Tag</td><td align="center" id="nametag"></td></tr>
                </tbody>
            </table>
            <br>
            <form method="POST" action="<? echo $this->site_url() . $this->uri->uri_string; ?>?switcher=form&for=upload" enctype="multipart/form-data" callback="showFileKMZ">
                <div class="row">
                    <div class="col-12 u-margin-b-10">
                        <label>Update and Upload File : </label>
                        <input type="file" name="fileupload">
                     </div>
                </div>
                <div class="row">
                    <div class="col-12 u-margin-b-10">
                        <input class="mybutton" type="submit" value="UPLOAD">
                     </div>
                </div>
            </form>
        </div>
        <?
        }
    }
    
	function Datalist($pageLimit){
        
        $thead =  "<thead>
                        <tr class=\"rowheader\">
                        <th align=center>No</th>	
                        <th align=center>Title</th>	
                        <th align=center>File Name</th>
                        <th align=center>mime</th>
                        <th align=center>src</th>
                        <th align=center>Publish</th>
                        <th align=\"center\">Created by</th>
                        <th align=center>Created Time</th>
                    </tr>
                </thead>"; 
        //Pengambilan data
        $uriUpdate = "?switcher=form&for=upload";
        $uriView = "?switcher=view";
        $uriDelete = "?switcher=delete";
        $uriPosting = "?switcher=posting";
        $kar = $this->Setup_datakaryawan->selectOpt();
        $r = $this->Uploadfile->listfile($pageLimit);
        $n = 1;
        $table = "";
        if(count($r) > 0){
            $no = 0 + $pageLimit[0];
            foreach($r as $k=>$v){
                $no++;
                $action =array();
                $action['view'] = "viewAction('{$uriView}&id={$v['id']}');";
                if($v['publish'] == 1){
                    $sttd = "Publish";	
                }else{
                    $action['posting'] = "postingAction('{$uriPosting}&id={$v['id']}');";
                    $action['delete'] = "deleteAction('{$uriDelete}&id={$v['id']}');";
                    $sttd = "-";
                }
                $action = $this->toAtrr($action);
                $table .= "<tr class=\"rowcontent\" list-action ".$action.">";
                $table .= "<td align=\"center\">{$no}</td>";
                $table .= "<td align=\"left\">{$v['name']}</td>";
                $table .= "<td align=\"center\">{$v['namefile']}</td>";
                $table .= "<td align=\"center\">{$v['mimes']}</td>";
                $table .= "<td align=\"left\">{$v['src']}</td>";
                $table .= "<td align=\"left\">{$v['publish']}</td>";
                $table .= "<td align=\"left\">{$kar[$v['createby']]}</td>";
                $table .= "<td align=\"left\">{$v['createtime']}</td>";
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
        $option['javascript']['src'] = array(
            $this->base_url().'js/'.$SELF.'.js?version='.time().'',
        );

        $d=array();
        $d['title'] = "Form Entry Data";
        $d['slave'] = "form";
        $d['text'] = "new";
        $d['show'] = true;
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
        $d['slave'] = "filter";
        $d['text'] = "Filter";
        $d['width'] = "300px";
        $d['show'] = false;
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