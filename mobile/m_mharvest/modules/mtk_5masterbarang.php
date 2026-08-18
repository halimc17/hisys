<?
defined('BASEPATH') OR exit('No direct script access allowed');
class Mtk_5masterbarang extends OWL_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('Mtk_masterbarang');
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
                echo $this->Datalist();
            break;
            case'insert':
                $dataInsert = array(
                    "kode"=>$this->post('kode'),
                    "decription"=>$this->post('deskripsi')
                );
                $QINSERT = $this->query_insert($dataInsert);
                if(!$result = $this->exec($QINSERT)){
                    $this->response['message'] = "Gagal Insert";
                }
                echo json_encode($this->response);
            break;
            case'form':
                $this->Form();
            break;
        }

    }
    function Form(){
            ?>
                <div class="body-frame u-margin-10">
                    <form method="POST" action="<? echo $this->site_url().$this->uri->uri_string;?>?switcher=insert" callback="callafterSimpan">
                        <div class="row">
                            <div class="col-12 u-margin-b-10">
                                <label>Kode</label>
                                <input name="kode" type="text" value="" required="">
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-12 u-margin-b-10">
                                <label>Deskripsi</label>
                                <input name="deskripsi" type="text" value="" required="">
                            </div>
                        </div>
                        <div class="row">
                            <input name="submit" type="submit" value="Save" >
                        </div>
                    </form>                   
                </div>
            <?php

    }
    function Filter(){

    }
    function Datalist(){
        $data = $this->Mtk_masterbarang->selectOpt();
        $tab = '<table class="sortable data-table full-width" data-print="true" data-action="true">';
        $tab .='<thead>';
        $tab .='<tr>';
        $tab .='<th width="200px">Kode</th>';
        $tab .='<th>Deskripsi</th>';
        $tab .='</tr>';
        $tab .='</thead>';
        $tab .='<tbody>';
        if(count($data) > 0){
        foreach($data as $k=>$v){
            $tab .='<tr>';
            $tab .='<td>{$k}</td>';
            $tab .='<td>{$v}</td>';
            $tab .='</tr>';
        }
    }else{
        $tab .='<tr>';
            $tab .='<td colspan="2">No data</td>';
            $tab .='</tr>';
    }
        $tab .='</tbody>';
        $tab .='</table>';
        return $tab;
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
        $d['slave'] = "Filter";
        $d['text'] = "Filter";
        $d['width'] = "300px";
        $d['show'] = true;
        $d['isEnable'] = true;
        $option['filter'] = $d;

        $option['breadcrumb']['title'] = $breadcrumb;

        $option['excel']['show']= true;
        $option['pdf']['show']= true;
        $option['csv']['show']= true;
        $option['fixHeader']['show']= false;
        $option['actions'] = array();
        $option['pathinfo']['site_url'] = $this->site_url();
        $option['pathinfo']['base_url'] = $this->base_url();
        $OPT =  json_encode($option);
        return $OPT;
    }
}
?>