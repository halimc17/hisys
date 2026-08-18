<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Uploadmapfile extends OWL_Controller{
    public function __construct(){
		parent::__construct();
		$this->load->model('Uploadfile');
		$this->load->model('Setup_datakaryawan');
		$this->load->model('Setup_attribute');
        $this->load->model('Blok');
		$this->load->library('GeoJson');
    }
    function slave(){
      $case = $this->get('switcher');
        switch($case){
            case'pdf':
            case'excel':
            case'csv':
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
                if($getData){
                    $tab->total_rows = $getData->rowCount();
                }else{
                    $tab->total_rows = 0;
                }
                //row has definition
                $tab->per_page = 30;//LIMIT : default 20
                $tab->cur_page = $page;
                $starting_limit = ($page-1)*$tab->per_page;
                //get Data per page == 
				// $tab->type_load = 'AUTO';
                $dataTable =  $this->Datalist($this->Uploadfile->listfile([$starting_limit,$tab->per_page]),$starting_limit);
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
                $data = $this->Uploadfile->getfile();
                if($this->get('for') != 'upload'){
                    echo $data->json();
                }else{
                    echo json_encode($this->Uploadfile->upload());
                }
            break;
            case'openxml':
                $data = $this->Uploadfile->getfileUri($this->get('path'));
                echo $data->json();
            break;
            case'set-style':
                $data = array(
                    'file_id'=>$this->post('id'),
                    'style_id'=>$this->post('styleid'),
                    'style_name'=>$this->post('stylename'),
                    'type'=>$this->post('type'),
                    'name'=>$this->post('name'),
                    'setvalue'=>$this->post('setvalue')
                );
                if($ex = $this->Uploadfile->set_style($data)){
                    $this->response['message'] = $ex;//"Changed";
                }else{
                    $this->response['message'] = $ex;//"Failed to Change!!";
                }
                echo json_encode($this->response);
            break;
            case'set-name':
                $data = array(
                    'id'=>$this->post('id'),
                    'name'=>$this->post('name')
                );
                if($ex = $this->Uploadfile->set_name($data)){
                    $this->response['message'] = $ex;//"Changed";
                }else{
                    $this->response['message'] = $ex;//"Failed to Change!!";
                }
                echo json_encode($this->response);
            break;
            case'geojsontemp':
                echo json_encode($this->Uploadfile->upload_geojson());
            break;
            case'getproperties':
                $prop = $this->Uploadfile->selectStyle($this->get('id'),$this->get('styleid'));
                $result = array();
                if(count($prop)>0){
                    foreach($prop as $v){
                        $result[$v['style_id']]['type'] = array($v['type'],$v['setvalue']);
                        $result[$v['style_id']][$v['name']] = array(,$v['setvalue']);
                    }
                }
                echo json_encode($prop);
            break;
            case'marker-name':
                $key = $this->get('key');
                $data = $this->get_style('marker-name');
                if(isset($data[$key])){
                    $result = $data[$key];
                }else{
                    $result = $data['point'];
                }
                echo json_encode($result);
            break;
            case'stroke-color':
                $this->get('key');
                echo json_encode($this->get_style('stroke-color'));
            break;
            case'fill-color':
                $this->get('key');
                echo json_encode($this->get_style('fill-color'));
            break;
            case'get-blok':
                $dataBlock = $this->Blok->getDataBlok("where status = 'A'");
                $dataBlockVal = array_column($dataBlock,'kodeorg','kodeorg');
                // $dataBlock = $this->Blok->getDataBlokInduk();
                // $dataBlockVal = array_column($dataBlock,'namaindukblok','indukblok');
                echo json_encode($dataBlockVal);
            break;
            case'upload_form':
                $this->upload_form_layer($this->get('layer'));
            break;
            case'form':
                $this->LoadView();
            break;
            case'description':
            break;
            case'filter':
            break;
            case'view': 
                $this->LoadView($this->get('id'));
            break;
            case 'unpublish':
                $exec = $this->Uploadfile->unpublish($this->get('id'));
                echo json_encode($exec);
            break;
            case'delete':
                $exec = $this->Uploadfile->deleteData($this->get('id'));
                echo json_encode($exec);
            break;
        }
    }
	function upload_file(){ 
        $this->Uploadfile->upload();
    }
    function upload_file_geojson(){ 
        $this->Uploadfile->upload_geojson();
    }
    function get_style($name){ 
        $result = array();
        $data['marker-name'] = array(
                'pokok'=>array(''=>'Choose Marker','circle'=>'Circle','pokok'=>'Palm Oil'),
                'tph'=>array(''=>'Choose Marker','circle'=>'Circle'),
                'kantor'=>array(''=>'Choose Marker','circle'=>'Circle'),
                'point'=>array(''=>'Choose Marker','circle'=>'Circle')
            );
        $data['stroke-color'] = array(''=>'Choose Color','#567f9a'=>'Default','#ff0000'=>'Red','#bebebe'=>'Grey');
        $data['fill-color'] = array(''=>'Choose Color','#ffffff'=>'Default','#f5ff9f'=>'Yellow','#bebebe'=>'Grey');
        if(isset($data[$name])){
            $result = $data[$name];
        }
        return $result;
    }

    function upload_form_layer($layer=0){ ?>
        <div class="u-padding-20 full-width full-height" style="background:#567f9a;position:sticky;top:0;z-index:2;">
            <form method="POST" action="<? echo $this->site_url().$this->uri->uri_string; ?>?switcher=xml&for=read" enctype="multipart/form-data" callback="showLayerGeojson">
                <div class="row">
                    <input type="hidden" name="layer" value="<? echo $layer; ?>">
                    <div class="col-12 u-margin-b-10">
                        <label for="fileupload" class="drop-container" id="dropcontainer">
                            <span class="drop-title">Drop files here</span>
                                or
                            <input type="file" name="fileupload" id="fileupload" required>
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

        <?
    }
    function upload_form(){ ?>
        <div class="u-padding-5 full-width" style="background:#567f9a;position:sticky;top:0;z-index:2;">
            <form method="POST" action="<? echo $this->site_url().$this->uri->uri_string; ?>?switcher=xml&for=read" enctype="multipart/form-data" callback="showFileGeojson">
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

        <?
    }
    function component_setup($dt){
        $this->load->lib('MakeHTML');
        $prop = $this->Uploadfile->selectStyle($dt['id']);
        $bgColor = 'bg-red';
        if($dt['layer'] != '0'){
            $delete = 'delete="removeFile(\''.$dt['id'].'\')"';
            // $bgColor = 'bg-green';
        }
        $marker_name = $this->Setup_attribute->marker_name();
        if(count($marker_name) > 0){
            $type = $marker_name;
        }else{
            $type = array();
        }
        $propDef = array(
            'stroke-color'=>'display:none;',
            'fill-color'=>'display:none;',
            'marker-name'=>'display:none;'
        );
        $func = $this->lib->MakeHTML;
        
        $selected = array();
        $properties = array();
        $isBlok = false;
        if(count($prop) > 0){
            foreach($prop as $v){
                if($v['type'] == 'blok'){
                    $isBlok = true;
                }
                if(isset($propDef[$v['name']])){
                    unset($propDef[$v['name']]);
                }
                if(!empty($this->get_style($v['name']))){
                    $opt = array();
                    if($v['name'] == 'marker-name'){
                        $dataOpt = $this->get_style($v['name'])[$v['type']];
                    }else{
                        $dataOpt = $this->get_style($v['name']);
                    }
                    foreach($dataOpt as $key=>$value){
                        $d = array();
                        $d['key'] = $key;
                        $d['value'] = $value;
                        $opt[]=(object)$d;
                    }
                    if(count($opt) > 0){
                        $properties[$v['name']] = $func->options($opt,$v['setvalue']);
                    }
                }
                if(isset($type[$v['type']])){
                    $selected[$v['type']] = 'selected';
                }else{
                    $selected[$v['type']] = '';
                }
            }
        }
        
        ?>
        <div>
            <div id="collection_<?php echo @$dt['id']; ?>" class="form-frame" show="<?php echo @$dt['src']; ?>" style="margin:5px;" list-action="true" <? echo @$delete;?>>
                <div class="title <?php echo $bgColor;?>">
                    <i class="fa fa-map-o u-margin-r-10"></i>
                    <span>Component <?php echo @$dt['idx']; ?></span>
                </div>
                <div class="body-frame">
                <table class="full-width" >
                    <tbody>
                        <tr><td align="left">Type</td><td align="left">
                            <select id="type_<?php echo @$dt['id']; ?>" name="type" class="full-width" onchange="setIcon(this.value,'<?php echo @$dt['id']; ?>')" required>
                                <? foreach($type as $k=>$v){ ?>
                                    <option value="<? echo $k;?>" <? echo @$selected[$k]?>><? echo $v;?></option>
                                <? } ?>
                            </select>
                        </td></tr>
                        <tr>
                            <td align="left">
                                <div class="input-group">Name 
                                    <span display="<?php ($isBlok)?>" class="input-group-addon pointer" onclick="$.z.elSearch('dataname_<?php echo @$dt['id']; ?>',event,setValTo)">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                    </span>
                                </div></td>
                            <td align="left">
                                <input id="name_<?php echo @$dt['id']; ?>" name="name" class="full-width" type="text" value="<?php echo @$dt['name']; ?>"  required>
                                <select id="dataname_<?php echo @$dt['id']; ?>" style="display:none;">
                                <?php 
                                    if($isBlok){
                                    $dataBlock = $this->Blok->getDataBlok("where status = 'A'");
                                    $dataBlockVal = array_column($dataBlock,'kodeorg','kodeorg');
                                    // $dataBlock = $this->Blok->getDataBlokInduk();
                                    // $dataBlockVal = array_column($dataBlock,'namaindukblok','indukblok');
                                    if(count($dataBlockVal) > 0){
                                    foreach($dataBlockVal as $k=>$v){
                                        echo '<option value="'.$k.'">'.$v.'</option>';
                                    }}}
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr id="stroke_color<?php echo @$dt['id']; ?>" style="<? echo @$propDef['stroke-color'];?>"><td align="left">Stoke Color</td><td align="left"><select id="stroke_color_val<?php echo @$dt['id']; ?>" name="stroke-color" class="full-width properties"><? echo @$properties['stroke-color']; ?></select></td></tr>
                        <tr id="fill_color<?php echo @$dt['id']; ?>" style="<? echo @$propDef['fill-color'];?>"><td align="left">Fill Color</td><td align="left"><select id="fill_color_val<?php echo @$dt['id']; ?>" name="fill-color" class="full-width properties"><? echo @$properties['fill-color']; ?></select></td></tr>
                        <tr id="marker_name<?php echo @$dt['id']; ?>" style="<? echo @$propDef['marker-name'];?>"><td align="left">Pin / Icon</td><td align="left"><select id="marker_name_val<?php echo @$dt['id']; ?>" name="marker-name" class="full-width properties"><? echo @$properties['marker-name']; ?></select></td></tr>
                    </tbody>
                </table>

                </div>    
            </div>
            <input id="style_collection_<?php echo @$dt['id']; ?>" class="mybutton u-margin-l-5 u-margin-r-5" type="button" value="Layer Style" onclick="$.Alert('File Belum tersedia!');">

        </div>
        <?
    }
    function toolsMap($id=""){ 
        if($id!=""){
            $result = $this->Uploadfile->getData($id);
        }
        ?>
        <div style="position: sticky; top: 0px;">
            
        </div>
        <?
            if($id==""){
                $this->upload_form();
            }else{
                $publish = 0;
                foreach($result as $k=>$v){
                    $publish = (int)$v['publish'];
                    $this->component_setup($v);
                }
                $display = '';
                if($publish == 1){
                    $display = 'display:none;';
                }
                echo '<br>';
                echo '<br>';
                echo '<div style="position:sticky; bottom:0px;background: #c4d6f0;padding: 10px 5px;box-shadow: 0px 1px 3px;">';
                echo '<input class="mybutton u-margin-l-5 u-margin-r-5" type="button" value="Upload" onclick="openUpload_form(\''.$id.'\')">';
                echo '<input id="publishbtn" style="'.$display.'" class="mybutton" type="button" value="Publish" onclick="publish_geojson()">';
                echo '</div>';
                
            }
    }
        function LoadView($id=""){ 
        ?>
        <div style="width:70%;height:100%;position: absolute;left: 0px;">
         <?php include(VIEWPATH.'map_clean.php'); 
         ?>
        </div>
        <div id="toolsmaps" data-id="<? echo $id; ?>" style="width:30%;height:100%;position:absolute;right:0px;overflow-y:scroll;border-left: 1px solid #ccc;" data-action="map_clean_0">
            <? $this->toolsMap($id); ?>
        </div>
        <?
    }
    
	function Datalist($data=array(),$no){
        
        $thead =  "<thead>
                        <tr class=\"rowheader\">
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
        $uriPosting = "?switcher=unpublish";
        $kar = $this->Setup_datakaryawan->selectOpt();
        $n = 1;
        $table = "";
        if(count($data) > 0){
            foreach($data as $k=>$v){
                $no++;
                $action =array();
                $action['view'] = "viewAction('{$uriView}&id={$v['id']}');";
                if($v['publish'] == 1){
                    $sttd = "Publish";	
                    $action['unpublish'] = "postingAction('{$uriPosting}&id={$v['id']}');";
                }else{
                    $action['delete'] = "deleteAction('{$uriDelete}&id={$v['id']}');";
                    $sttd = "-";
                }
                $action = $this->toAtrr($action);
                $table .= "<tr class=\"rowcontent\" list-action ".$action.">";
                $table .= "<td align=\"left\">{$v['name']}</td>";
                $table .= "<td align=\"center\">{$v['namefile']}</td>";
                $table .= "<td align=\"center\">{$v['mimes']}</td>";
                $table .= "<td align=\"left\">{$v['src']}</td>";
                $table .= "<td align=\"left\">{$sttd}</td>";
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