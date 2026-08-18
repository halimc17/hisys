<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Upload_filekml extends OWL_Controller{
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
            try{
                //get request halaman
                $page = ((int)$this->get('page')==0)?1:(int)$this->get('page');
                ////////////////////////////
                //Pengambilan Data Total Row
                $getData = $this->Uploadfile->selectQuery();
                //Pagination setup and load
                $this->load->lib("Pagination","paging");
                $tab = $this->lib->paging;
                $tab->id = "upload_filekml";
                $tab->total_rows = (int)$getData->rowCount();
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
            }catch(Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            break;
            case'xml':
                $data = $this->getfile();
                if($this->get('for') != 'upload'){
                    if($data != null and count($data) > 0 ){
                        echo json_encode($data);
                    }
                }else{
                    $this->upload_file();
                }
            break;
            case'openxml':
                $info = pathinfo($this->get('path'));
                $nameFile = $info['basename'];
                $data = $this->openfile($this->get('path'),$nameFile);
                if($this->get('for') != 'upload'){
                    if($data != null and count($data) > 0 ){
                        echo json_encode($data);
                    }
                }else{
                    $this->upload_file();
                }
            break;
            case'form':
                $this->LoadView();
            break;
            case'filter':
            break;
            case'view': 
                $this->LoadView($this->get('id'));
            break;
        }
    }
	function upload_file(){ 
        $this->Uploadfile->upload();
    }
    private function makeDataFile($data=array(),$nameFile){
        $fileApproved = array('kml','json','svg');
        // $fileApproved = array('kml','json','svg','xsl');
        $result = null;
        if(isset($data['content'])){
            if(isset($data['name'])){
                $data['info'] = pathinfo($data['name']);
            }
            $data['fileupload'] = $nameFile;
            if(isset($data['info']) and in_array(strtolower($data['info']['extension']),$fileApproved)){
                if($data['info']['extension'] == 'kml'){
                    $content = fopen('data://text/plain,' .$data['content'],'r');
                }elseif($data['info']['extension'] == 'svg'){
                    $content = fopen($data['content'], "r");
                }else{
                    $content = fopen($data['content'], "r");
                }
                $fileContent = "";
                while (!feof($content)) {
                    $fileContent .= fgets($content);
                }
                fclose($content);
                $data['content'] = $fileContent;  

                $result = $data;
            }else{
                // $content = fopen($data['content'], "r");
            }
        }
        return $result;
    }
    function openfile($file_tmpname,$nameFile){
        $file = Null;
        $info = pathinfo($nameFile);
        $fileApproved = array('kmz','zip');
        if(in_array(strtolower(@$info['extension']),$fileApproved)){
            $zip = zip_open($file_tmpname);
            $content = array();
            if($zip){
                $count = 0;
                while ($zip_entry = zip_read($zip)) {
                    $count++;
                    if (zip_entry_open($zip, $zip_entry, "r")) {
                        $res = array();
                        $res['name'] = zip_entry_name($zip_entry);
                        $res['content'] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                        $content[] = $res;
                    }
                }	
                zip_close($zip);
                $fps = array();
                foreach($content as $c){
                    if($res = $this->makeDataFile($c,$nameFile)){
                        $fps[] = $res;
                    }
                }
                if(count($fps) > 0){
                    $file = $fps;
                }
            }
        }else{
            // exit('ERROR : File tidak di izinkan');
            $res = array();
            $res['name'] = $nameFile;
            $res['content'] = $file_tmpname;
            if($res = $this->makeDataFile($res,$nameFile)){
                $fps[] = $res;
                $file = $fps;
            }
        }
        return $file;
    }
    function getfile(){
        $file = Null;
        $nameFile = str_replace(" ","_",$_FILES["fileupload"]['name']);
        if (isset($_FILES["fileupload"])){
            $file_tmpname = $_FILES['fileupload']['tmp_name'];
            $file = $this->openfile($file_tmpname,$nameFile);
            return $file;
        }       
    }
    function LoadView($id=""){ 
        $file = Null;
        if($id!=""){
            $data = $this->Uploadfile->selectbyId($id);
            if(count($data)> 0){
                $data = array_shift($data);
                $src = $data['src'];
                $info = pathinfo($src);
                $nameFile = $info['basename'];
                $location = 'm_fileDocuments';
                $base = $this->base_url('',$location);
                $src = $location."/".str_replace($base,'',$src);
                // $file = $this->openfile($src,$nameFile);
                // $data = json_encode($file);
            }
        }
        ?>
        <div id="mapview" show="<?php echo @$src;?>" style="width:60%;height:100%;position: absolute;left: 0px;">
         <?php include(VIEWPATH.'map_clean.php'); 
         ?>
        </div>
        <div style="width:40%;height:100%;position: absolute;right:0px;">
            <div class="u-padding-5 full-width" style="background:#567f9a;position:sticky;top:0;z-index:2;">
                <form method="POST" action="<? echo $this->site_url() . $this->uri->uri_string; ?>?switcher=xml&for=read" enctype="multipart/form-data" callback="showFileKMZ">
                    <div class="row">
                        <div class="col-12 u-margin-b-10">
                            <label for="fileupload" class="drop-container" id="dropcontainer">
                                <span class="drop-title">Drop files here</span>
                                or
                                <input type="file" name="fileupload" id="fileupload" onchange="resetPreview(this)" required>
                            </label>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-12 u-margin-b-10">
                            <input class="mybutton" name="submit" type="submit" value="Preview">
                        </div>
                    </div>
                </form>
                <br>
                <div class="clearfix"></div>
            </div>
            <table id="metadata" class="sortable data-table full-width">
            <thead>
                     <tr class="rowheader">
                        <th align="left">Komponen</th>	
                        <th align="left">Value</th>	
                    </tr>
                </thead>
                <tbody>
                    <tr><td align="left"><?php echo @$data['namefile'].".".@$data['mimes']; ?></td><td align="center"><?php echo @$data['name']; ?></td></tr>
                    <tr><td align="left">ID</td><td align="left" id="idtag"></td></tr>
                    <tr><td align="left">NAME</td><td align="left" id="nametag"></td></tr>
                    <tr><td align="left">COLOR</td><td align="left"><select id="colortag"><option value="#ffffff">White</option><option value="#FF0000">Red</option></select></td></tr>
                    <tr><td align="left">Description</td><td align="left" id="description"></td></tr>
                </tbody>
            </table>
        </div>
        <?
        
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
        $d['event']['click'] = "newUploadFile";
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